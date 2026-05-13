<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    private function normalizeCountryValue(string $value): string
    {
        $normalized = Str::of($value)
            ->ascii()
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', ' ');

        return (string) $normalized;
    }

    private function resolveCountryId(Request $request): void
    {
        if ($request->filled('country_id')) {
            $request->merge(['country_id' => (int) $request->input('country_id')]);
            return;
        }

        if (!$request->filled('country_name')) {
            return;
        }

        $countryNameInput = trim((string) $request->input('country_name'));
        $countryInput = $this->normalizeCountryValue($countryNameInput);
        $countryCodeInput = strtoupper(trim((string) $request->input('country_code', '')));

        $country = Country::query()->get(['id', 'name', 'code'])->first(function (Country $country) use ($countryInput) {
            return $this->normalizeCountryValue((string) $country->name) === $countryInput
                || $this->normalizeCountryValue((string) $country->code) === $countryInput;
        });

        if ($country) {
            $request->merge(['country_id' => $country->id]);
            return;
        }

        // Fallback: cria o país automaticamente quando vem da API externa e ainda não existe na tabela local.
        if ($countryCodeInput !== '' && strlen($countryCodeInput) === 2) {
            $createdCountry = Country::firstOrCreate(
                ['code' => $countryCodeInput],
                [
                    'name' => $countryNameInput,
                    'active' => true,
                ]
            );

            $request->merge(['country_id' => $createdCountry->id]);
        }
    }

    public function index(Request $request)
    {
        $query = Entity::query();

        if ($request->filled('type')) {
            $requestedType = $request->input('type');

            if ($requestedType === 'client') {
                $query->where(function ($typeQuery) {
                    $typeQuery->where('type', 'client')
                        ->orWhere('type', 'both');
                });
            } elseif ($requestedType === 'supplier') {
                $query->where(function ($typeQuery) {
                    $typeQuery->where('type', 'supplier')
                        ->orWhere('type', 'both');
                });
            } else {
                $query->where('type', $requestedType);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nif', 'like', "%{$search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $this->resolveCountryId($request);

        $data = $request->validate([
            'type'         => ['required', Rule::in(['client', 'supplier', 'both'])],
            'nif'          => 'required|string|max:20|unique:entities,nif',
            'name'         => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'postal_code'  => 'required|string|max:20',
            'city'         => 'required|string|max:100',
            'country_id'   => 'required|exists:countries,id',
            'phone'        => 'nullable|string|max:20',
            'mobile'       => 'nullable|string|max:20',
            'website'      => 'nullable|url|max:255',
            'email'        => 'nullable|email|max:255',
            'rgpd_consent' => 'boolean',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
        ]);

        $data['number'] = $this->generateEntityNumber();

        return response()->json(Entity::create($data), 201);
    }

    private function generateEntityNumber(): string
    {
        do {
            $candidate = 'ENT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (Entity::where('number', $candidate)->exists());

        return $candidate;
    }

    public function show($id)
    {
        return Entity::with(['country', 'contacts'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $entity = Entity::findOrFail($id);

        $this->resolveCountryId($request);

        $data = $request->validate([
            'type'         => ['sometimes', Rule::in(['client', 'supplier', 'both'])],
            'nif'          => ['sometimes', 'string', 'max:20', Rule::unique('entities', 'nif')->ignore($entity->id)],
            'name'         => 'sometimes|string|max:255',
            'address'      => 'sometimes|string|max:255',
            'postal_code'  => 'sometimes|string|max:20',
            'city'         => 'sometimes|string|max:100',
            'country_id'   => 'sometimes|exists:countries,id',
            'phone'        => 'nullable|string|max:20',
            'mobile'       => 'nullable|string|max:20',
            'website'      => 'nullable|url|max:255',
            'email'        => 'nullable|email|max:255',
            'rgpd_consent' => 'boolean',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
        ]);

        $entity->update($data);

        return response()->json($entity);
    }

    public function destroy($id)
    {
        Entity::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    /**
     * Validate a European VAT number via the VIES web service.
     *
     * @param  string  $nif  Full VAT number with country code prefix, e.g. PT123456789
     */
    public function viesValidate($nif)
    {
        $nif = strtoupper(trim($nif));

        if (strlen($nif) < 3) {
            return response()->json([
                'error' => 'NIF invalido. Use o formato CCXXXXXXXXX (ex: PT123456789).',
            ], 422);
        }

        $countryCode = substr($nif, 0, 2);
        $vatNumber   = substr($nif, 2);

        if (!ctype_alpha($countryCode)) {
            return response()->json(['error' => 'Codigo de pais invalido.'], 422);
        }

        try {
            $response = Http::timeout(10)
                ->post('https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number', [
                    'countryCode' => $countryCode,
                    'vatNumber'   => $vatNumber,
                ]);

            if ($response->serverError()) {
                return response()->json(['error' => 'Servico VIES indisponivel. Tente mais tarde.'], 503);
            }

            $data = $response->json();

            if (!($data['valid'] ?? false)) {
                return response()->json([
                    'valid'   => false,
                    'message' => 'NIF nao registado no VIES.',
                ], 404);
            }

            return response()->json([
                'valid'        => true,
                'country_code' => $data['countryCode'] ?? $countryCode,
                'vat_number'   => $data['vatNumber']   ?? $vatNumber,
                'name'         => $data['name']         ?? null,
                'address'      => $data['address']      ?? null,
            ]);
        } catch (\Exception) {
            return response()->json(['error' => 'Erro ao contactar o servico VIES.'], 503);
        }
    }
}
