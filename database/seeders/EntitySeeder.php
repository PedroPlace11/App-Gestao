<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactFunction;
use App\Models\Country;
use App\Models\Entity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $countryId = Country::query()
            ->where('code', 'PT')
            ->value('id') ?? Country::query()->value('id');

        if (!$countryId) {
            return;
        }

        $contactFunctionId = ContactFunction::query()
            ->where('name', 'Gerente')
            ->value('id');

        foreach ($this->sampleEntities($countryId) as $entityData) {
            $contactData = $entityData['contact'];
            unset($entityData['contact']);

            $entity = $this->upsertEntity($entityData);

            $this->upsertContact($entity, [
                'number' => sprintf('C-%d-001', $entity->id),
                'first_name' => $contactData['first_name'],
                'last_name' => $contactData['last_name'],
                'function_id' => $contactFunctionId,
                'phone' => $contactData['phone'],
                'mobile' => $contactData['mobile'],
                'email' => $contactData['email'],
                'rgpd_consent' => true,
                'observations' => $contactData['observations'],
                'active' => true,
            ]);
        }
    }

    private function sampleEntities(int $countryId): array
    {
        return [
            [
                'number' => 'E-0001',
                'type' => 'client',
                'nif' => 'PT501234560',
                'name' => 'Cliente Exemplo Lda',
                'address' => 'Rua das Empresas, 45',
                'postal_code' => '1000-001',
                'city' => 'Lisboa',
                'country_id' => $countryId,
                'phone' => '+351210000001',
                'mobile' => '+351910000001',
                'website' => 'https://cliente-exemplo.test',
                'email' => 'geral@cliente-exemplo.test',
                'rgpd_consent' => true,
                'observations' => 'Cliente criado automaticamente pelo seeder.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Ana',
                    'last_name' => 'Silva',
                    'phone' => '+351210000003',
                    'mobile' => '+351930000003',
                    'email' => 'ana.silva@cliente-exemplo.test',
                    'observations' => 'Contacto principal do cliente exemplo.',
                ],
            ],
            [
                'number' => 'E-0002',
                'type' => 'supplier',
                'nif' => 'PT501234578',
                'name' => 'Fornecedor Exemplo SA',
                'address' => 'Avenida Industrial, 120',
                'postal_code' => '4450-718',
                'city' => 'Matosinhos',
                'country_id' => $countryId,
                'phone' => '+351220000002',
                'mobile' => '+351920000002',
                'website' => 'https://fornecedor-exemplo.test',
                'email' => 'compras@fornecedor-exemplo.test',
                'rgpd_consent' => true,
                'observations' => 'Fornecedor criado automaticamente pelo seeder.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Miguel',
                    'last_name' => 'Costa',
                    'phone' => '+351220000004',
                    'mobile' => '+351940000004',
                    'email' => 'miguel.costa@fornecedor-exemplo.test',
                    'observations' => 'Contacto principal do fornecedor exemplo.',
                ],
            ],
            [
                'number' => 'E-0003',
                'type' => 'client',
                'nif' => 'PT501234586',
                'name' => 'Atlantico Retail Unipessoal',
                'address' => 'Rua do Comercio, 88',
                'postal_code' => '4700-210',
                'city' => 'Braga',
                'country_id' => $countryId,
                'phone' => '+351253100010',
                'mobile' => '+351913100010',
                'website' => 'https://atlantico-retail.test',
                'email' => 'compras@atlantico-retail.test',
                'rgpd_consent' => true,
                'observations' => 'Cliente de retalho para testes.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Rita',
                    'last_name' => 'Ferreira',
                    'phone' => '+351253100011',
                    'mobile' => '+351933100011',
                    'email' => 'rita.ferreira@atlantico-retail.test',
                    'observations' => 'Contacto comercial principal.',
                ],
            ],
            [
                'number' => 'E-0004',
                'type' => 'supplier',
                'nif' => 'PT501234594',
                'name' => 'Metalurgica Norte SA',
                'address' => 'Zona Industrial Lote 12',
                'postal_code' => '3750-501',
                'city' => 'Agueda',
                'country_id' => $countryId,
                'phone' => '+351234500020',
                'mobile' => '+351914500020',
                'website' => 'https://metalurgica-norte.test',
                'email' => 'vendas@metalurgica-norte.test',
                'rgpd_consent' => true,
                'observations' => 'Fornecedor industrial de estruturas metalicas.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Carlos',
                    'last_name' => 'Mendes',
                    'phone' => '+351234500021',
                    'mobile' => '+351934500021',
                    'email' => 'carlos.mendes@metalurgica-norte.test',
                    'observations' => 'Gestor de conta do fornecedor.',
                ],
            ],
            [
                'number' => 'E-0005',
                'type' => 'client',
                'nif' => 'PT501234602',
                'name' => 'Horizonte Hotelaria Lda',
                'address' => 'Avenida Central, 201',
                'postal_code' => '8000-321',
                'city' => 'Faro',
                'country_id' => $countryId,
                'phone' => '+351289600030',
                'mobile' => '+351915600030',
                'website' => 'https://horizonte-hotelaria.test',
                'email' => 'direcao@horizonte-hotelaria.test',
                'rgpd_consent' => true,
                'observations' => 'Cliente do setor hoteleiro.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Beatriz',
                    'last_name' => 'Sousa',
                    'phone' => '+351289600031',
                    'mobile' => '+351935600031',
                    'email' => 'beatriz.sousa@horizonte-hotelaria.test',
                    'observations' => 'Responsavel pelas compras.',
                ],
            ],
            [
                'number' => 'E-0006',
                'type' => 'supplier',
                'nif' => 'PT501234610',
                'name' => 'TecnoCabos Portugal',
                'address' => 'Parque Empresarial Edificio B',
                'postal_code' => '3020-456',
                'city' => 'Coimbra',
                'country_id' => $countryId,
                'phone' => '+351239700040',
                'mobile' => '+351916700040',
                'website' => 'https://tecnocabos-portugal.test',
                'email' => 'geral@tecnocabos-portugal.test',
                'rgpd_consent' => true,
                'observations' => 'Fornecedor de cablagem e consumiveis.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Joao',
                    'last_name' => 'Ramos',
                    'phone' => '+351239700041',
                    'mobile' => '+351936700041',
                    'email' => 'joao.ramos@tecnocabos-portugal.test',
                    'observations' => 'Contacto tecnico-comercial.',
                ],
            ],
            [
                'number' => 'E-0007',
                'type' => 'client',
                'nif' => 'PT501234628',
                'name' => 'Nova Linha Engenharia',
                'address' => 'Rua dos Projetos, 17',
                'postal_code' => '3500-112',
                'city' => 'Viseu',
                'country_id' => $countryId,
                'phone' => '+351232800050',
                'mobile' => '+351917800050',
                'website' => 'https://nova-linha-engenharia.test',
                'email' => 'projetos@nova-linha-engenharia.test',
                'rgpd_consent' => true,
                'observations' => 'Cliente de engenharia e instalacoes.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Ines',
                    'last_name' => 'Martins',
                    'phone' => '+351232800051',
                    'mobile' => '+351937800051',
                    'email' => 'ines.martins@nova-linha-engenharia.test',
                    'observations' => 'Coordenadora de projeto.',
                ],
            ],
            [
                'number' => 'E-0008',
                'type' => 'supplier',
                'nif' => 'PT501234636',
                'name' => 'Quimica Industrial Iberica',
                'address' => 'Rua da Industria Quimica, 9',
                'postal_code' => '2910-761',
                'city' => 'Setubal',
                'country_id' => $countryId,
                'phone' => '+351265900060',
                'mobile' => '+351918900060',
                'website' => 'https://quimica-iberica.test',
                'email' => 'comercial@quimica-iberica.test',
                'rgpd_consent' => true,
                'observations' => 'Fornecedor de produtos quimicos industriais.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Sofia',
                    'last_name' => 'Pereira',
                    'phone' => '+351265900061',
                    'mobile' => '+351938900061',
                    'email' => 'sofia.pereira@quimica-iberica.test',
                    'observations' => 'Gestora comercial da conta.',
                ],
            ],
            [
                'number' => 'E-0009',
                'type' => 'client',
                'nif' => 'PT501234644',
                'name' => 'Alvorada Facility Services',
                'address' => 'Rua do Parque, 54',
                'postal_code' => '9000-090',
                'city' => 'Funchal',
                'country_id' => $countryId,
                'phone' => '+351291100070',
                'mobile' => '+351919100070',
                'website' => 'https://alvorada-facility.test',
                'email' => 'operacoes@alvorada-facility.test',
                'rgpd_consent' => true,
                'observations' => 'Cliente de manutencao e servicos.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Tiago',
                    'last_name' => 'Araujo',
                    'phone' => '+351291100071',
                    'mobile' => '+351939100071',
                    'email' => 'tiago.araujo@alvorada-facility.test',
                    'observations' => 'Responsavel operacional.',
                ],
            ],
            [
                'number' => 'E-0010',
                'type' => 'supplier',
                'nif' => 'PT501234652',
                'name' => 'Energia Modular Equipamentos',
                'address' => 'Travessa da Tecnologia, 33',
                'postal_code' => '3810-205',
                'city' => 'Aveiro',
                'country_id' => $countryId,
                'phone' => '+351234110080',
                'mobile' => '+351914110080',
                'website' => 'https://energia-modular.test',
                'email' => 'vendas@energia-modular.test',
                'rgpd_consent' => true,
                'observations' => 'Fornecedor de equipamentos tecnicos.',
                'active' => true,
                'contact' => [
                    'first_name' => 'Mariana',
                    'last_name' => 'Oliveira',
                    'phone' => '+351234110081',
                    'mobile' => '+351934110081',
                    'email' => 'mariana.oliveira@energia-modular.test',
                    'observations' => 'Contacto comercial principal.',
                ],
            ],
        ];
    }

    private function upsertEntity(array $attributes): Entity
    {
        $entity = Entity::query()->firstOrNew(['number' => $attributes['number']]);

        foreach ($attributes as $key => $value) {
            $entity->{$key} = $value;
        }

        $entity->save();

        return $entity;
    }

    private function upsertContact(Entity $entity, array $attributes): void
    {
        $contact = Contact::query()->firstOrNew(['number' => $attributes['number']]);
        $contact->entity_id = $entity->id;

        foreach ($attributes as $key => $value) {
            $contact->{$key} = $value;
        }

        $contact->save();
    }
}
