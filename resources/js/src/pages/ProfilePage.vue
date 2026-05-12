<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';

type AuthUser = {
    id?: number;
    name?: string;
    email?: string;
    two_factor_secret?: string | null;
    two_factor_confirmed_at?: string | null;
};

const { toast } = useToast();
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const authUser = ref<AuthUser>({ ...(window as any).__AUTH_USER__ ?? {} });

const profileForm = reactive({
    name: '',
    email: '',
});

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const twoFactorForm = reactive({
    password: '',
    code: '',
});

const isSavingProfile = ref(false);
const isSavingPassword = ref(false);
const isProcessingTwoFactor = ref(false);
const qrCodeSvg = ref('');
const recoveryCodes = ref<string[]>([]);
const hasTwoFactorSecret = ref(Boolean(authUser.value.two_factor_secret));

const displayName = computed(() => profileForm.name.trim() || authUser.value.name || 'Utilizador');
const displayEmail = computed(() => profileForm.email.trim() || authUser.value.email || '-');
const initials = computed(() => {
    const name = displayName.value.trim();
    return name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase() || 'U';
});
const twoFactorEnabled = computed(() => hasTwoFactorSecret.value);
const twoFactorConfirmed = computed(() => Boolean(authUser.value.two_factor_confirmed_at));

const syncSidebarUser = (patch: Partial<AuthUser>) => {
    authUser.value = { ...authUser.value, ...patch };
    (window as any).__AUTH_USER__ = { ...(window as any).__AUTH_USER__ ?? {}, ...patch };
    window.dispatchEvent(new CustomEvent('auth-user-updated', { detail: authUser.value }));
};

const readResponseBody = async (response: Response) => {
    const text = await response.text();

    if (!text) {
        return null;
    }

    try {
        return JSON.parse(text);
    } catch {
        return text;
    }
};

const fortifyRequest = async <T>(endpoint: string, method: string, body?: Record<string, unknown>): Promise<T> => {
    const response = await fetch(endpoint, {
        method,
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            ...(body ? { 'Content-Type': 'application/json' } : {}),
            'X-CSRF-TOKEN': csrfToken,
        },
        body: body ? JSON.stringify(body) : undefined,
    });

    const payload = await readResponseBody(response);

    if (!response.ok) {
        const message = typeof payload === 'object' && payload !== null && 'message' in payload
            ? String((payload as Record<string, unknown>).message)
            : `HTTP ${response.status}`;

        throw new Error(message);
    }

    return payload as T;
};

const loadTwoFactorState = async () => {
    if (!hasTwoFactorSecret.value) {
        qrCodeSvg.value = '';
        recoveryCodes.value = [];
        return;
    }

    try {
        const [qrCodeResponse, recoveryCodesResponse] = await Promise.all([
            fortifyRequest<{ svg?: string }>('/user/two-factor-qr-code', 'GET'),
            fortifyRequest<string[]>('/user/two-factor-recovery-codes', 'GET'),
        ]);

        qrCodeSvg.value = qrCodeResponse?.svg ?? '';
        recoveryCodes.value = Array.isArray(recoveryCodesResponse) ? recoveryCodesResponse : [];
    } catch (error) {
        qrCodeSvg.value = '';
        recoveryCodes.value = [];
        toast({
            title: 'Nao foi possivel carregar a 2FA',
            description: error instanceof Error ? error.message : 'Tente novamente.',
            variant: 'destructive',
        });
    }
};

const updateProfile = async () => {
    isSavingProfile.value = true;

    try {
        await fortifyRequest('/user/profile-information', 'PUT', {
            name: profileForm.name,
            email: profileForm.email,
        });

        syncSidebarUser({
            name: profileForm.name,
            email: profileForm.email,
        });

        toast({
            title: 'Perfil atualizado',
            description: 'Nome e email guardados com sucesso.',
        });
    } catch (error) {
        toast({
            title: 'Erro ao guardar o perfil',
            description: error instanceof Error ? error.message : 'Verifique os dados introduzidos.',
            variant: 'destructive',
        });
    } finally {
        isSavingProfile.value = false;
    }
};

const updatePassword = async () => {
    isSavingPassword.value = true;

    try {
        await fortifyRequest('/user/password', 'PUT', {
            current_password: passwordForm.current_password,
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        });

        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';

        toast({
            title: 'Password atualizada',
            description: 'A password foi alterada com sucesso.',
        });
    } catch (error) {
        toast({
            title: 'Erro ao alterar a password',
            description: error instanceof Error ? error.message : 'Confirme os dados introduzidos.',
            variant: 'destructive',
        });
    } finally {
        isSavingPassword.value = false;
    }
};

const enableTwoFactor = async () => {
    if (!twoFactorForm.password) {
        toast({
            title: 'Confirme a password',
            description: 'Introduza a password atual para ativar a 2FA.',
            variant: 'destructive',
        });
        return;
    }

    isProcessingTwoFactor.value = true;

    try {
        await fortifyRequest('/user/confirm-password', 'POST', {
            password: twoFactorForm.password,
        });

        await fortifyRequest('/user/two-factor-authentication', 'POST');

        hasTwoFactorSecret.value = true;
        authUser.value = { ...authUser.value, two_factor_secret: 'enabled' };
        twoFactorForm.password = '';
        await loadTwoFactorState();

        toast({
            title: '2FA ativada',
            description: 'Leia o QR code e confirme o código na aplicação autenticadora.',
        });
    } catch (error) {
        toast({
            title: 'Erro ao ativar a 2FA',
            description: error instanceof Error ? error.message : 'Nao foi possivel ativar a autenticacao em dois fatores.',
            variant: 'destructive',
        });
    } finally {
        isProcessingTwoFactor.value = false;
    }
};

const confirmTwoFactor = async () => {
    if (!twoFactorForm.code) {
        toast({
            title: 'Introduza o codigo',
            description: 'Digite o codigo de 6 digitos gerado pela app autenticadora.',
            variant: 'destructive',
        });
        return;
    }

    isProcessingTwoFactor.value = true;

    try {
        await fortifyRequest('/user/confirmed-two-factor-authentication', 'POST', {
            code: twoFactorForm.code,
        });

        authUser.value = { ...authUser.value, two_factor_confirmed_at: new Date().toISOString() };
        twoFactorForm.code = '';

        toast({
            title: '2FA confirmada',
            description: 'A autenticacao em dois fatores ficou ativa.',
        });
    } catch (error) {
        toast({
            title: 'Erro ao confirmar a 2FA',
            description: error instanceof Error ? error.message : 'O codigo inserido nao e valido.',
            variant: 'destructive',
        });
    } finally {
        isProcessingTwoFactor.value = false;
    }
};

const disableTwoFactor = async () => {
    if (!window.confirm('Tem a certeza que pretende desativar a 2FA?')) {
        return;
    }

    isProcessingTwoFactor.value = true;

    try {
        await fortifyRequest('/user/two-factor-authentication', 'DELETE');

        hasTwoFactorSecret.value = false;
        qrCodeSvg.value = '';
        recoveryCodes.value = [];
        twoFactorForm.password = '';
        twoFactorForm.code = '';
        authUser.value = { ...authUser.value, two_factor_secret: null, two_factor_confirmed_at: null };

        toast({
            title: '2FA desativada',
            description: 'A autenticacao em dois fatores foi removida.',
        });
    } catch (error) {
        toast({
            title: 'Erro ao desativar a 2FA',
            description: error instanceof Error ? error.message : 'Nao foi possivel desativar a autenticacao em dois fatores.',
            variant: 'destructive',
        });
    } finally {
        isProcessingTwoFactor.value = false;
    }
};

const copyRecoveryCodes = async () => {
    if (!recoveryCodes.value.length) {
        return;
    }

    try {
        await navigator.clipboard.writeText(recoveryCodes.value.join('\n'));
        toast({
            title: 'Códigos copiados',
            description: 'As recovery codes foram copiadas para a área de transferência.',
        });
    } catch {
        toast({
            title: 'Nao foi possivel copiar',
            description: 'Copie os códigos manualmente.',
            variant: 'destructive',
        });
    }
};

onMounted(async () => {
    profileForm.name = authUser.value.name ?? '';
    profileForm.email = authUser.value.email ?? '';

    if (hasTwoFactorSecret.value) {
        await loadTwoFactorState();
    }
});
</script>

<template>
    <div class="profile-page">
        <section class="profile-hero">
            <div class="profile-hero__avatar">{{ initials }}</div>
            <div class="profile-hero__content">
                <p class="profile-kicker">Conta pessoal</p>
                <h1>{{ displayName }}</h1>
                <p>{{ displayEmail }}</p>
            </div>
            <div class="profile-hero__status">
                <div class="status-pill" :class="twoFactorConfirmed ? 'status-pill--success' : 'status-pill--warning'">
                    {{ twoFactorConfirmed ? '2FA confirmada' : (twoFactorEnabled ? '2FA pendente' : '2FA desativada') }}
                </div>
                <p class="profile-hero__hint">
                    Aqui pode alterar o nome, email, password e ativar a autenticação em dois fatores com o Fortify.
                </p>
            </div>
        </section>

        <div class="profile-grid">
            <Card>
                <CardHeader>
                    <CardTitle>Dados do perfil</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="profile-form" @submit.prevent="updateProfile">
                        <div class="field-group">
                            <Label for="profile-name">Nome</Label>
                            <Input id="profile-name" v-model="profileForm.name" placeholder="Nome de utilizador" />
                        </div>

                        <div class="field-group">
                            <Label for="profile-email">Email</Label>
                            <Input id="profile-email" v-model="profileForm.email" type="email"
                                placeholder="utilizador@empresa.pt" />
                        </div>

                        <div class="form-actions">
                            <Button type="submit" :disabled="isSavingProfile">
                                {{ isSavingProfile ? 'A guardar...' : 'Guardar perfil' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Alterar password</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="profile-form" @submit.prevent="updatePassword">
                        <div class="field-group">
                            <Label for="current-password">Password atual</Label>
                            <Input id="current-password" v-model="passwordForm.current_password" type="password"
                                autocomplete="current-password" />
                        </div>

                        <div class="field-group">
                            <Label for="new-password">Nova password</Label>
                            <Input id="new-password" v-model="passwordForm.password" type="password"
                                autocomplete="new-password" />
                        </div>

                        <div class="field-group">
                            <Label for="password-confirmation">Confirmar password</Label>
                            <Input id="password-confirmation" v-model="passwordForm.password_confirmation"
                                type="password" autocomplete="new-password" />
                        </div>

                        <div class="form-actions">
                            <Button type="submit" :disabled="isSavingPassword">
                                {{ isSavingPassword ? 'A guardar...' : 'Alterar password' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Segurança e 2FA</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="security-layout">
                    <div class="security-panel">
                        <p class="security-label">Estado atual</p>
                        <p class="security-title">
                            {{ twoFactorConfirmed ? 'Autenticacao em dois fatores ativa' : (twoFactorEnabled ? '2FA ativada, a aguardar confirmacao' : '2FA desativada') }}
                        </p>
                        <p class="security-text">
                            A 2FA é gerida diretamente pelos endpoints do Laravel Fortify. Quando estiver ativa, use a
                            aplicação autenticadora para gerar o código de 6 dígitos.
                        </p>

                        <div v-if="!twoFactorEnabled" class="field-group field-group--inline">
                            <Label for="two-factor-password">Password atual</Label>
                            <Input id="two-factor-password" v-model="twoFactorForm.password" type="password"
                                autocomplete="current-password" placeholder="Confirme para ativar a 2FA" />
                        </div>

                        <div v-if="twoFactorEnabled && !twoFactorConfirmed" class="field-group field-group--inline">
                            <Label for="two-factor-code">Codigo de confirmação</Label>
                            <Input id="two-factor-code" v-model="twoFactorForm.code" inputmode="numeric"
                                placeholder="123456" />
                        </div>

                        <div class="security-actions">
                            <Button v-if="!twoFactorEnabled" :disabled="isProcessingTwoFactor" @click="enableTwoFactor">
                                {{ isProcessingTwoFactor ? 'A ativar...' : 'Ativar 2FA' }}
                            </Button>
                            <template v-else>
                                <Button v-if="!twoFactorConfirmed" :disabled="isProcessingTwoFactor"
                                    @click="confirmTwoFactor">
                                    {{ isProcessingTwoFactor ? 'A confirmar...' : 'Confirmar 2FA' }}
                                </Button>
                                <Button variant="outline" :disabled="isProcessingTwoFactor" @click="disableTwoFactor">
                                    Desativar 2FA
                                </Button>
                            </template>
                        </div>
                    </div>

                    <div class="security-panel security-panel--soft">
                        <p class="security-label">QR code</p>
                        <div v-if="qrCodeSvg" class="qr-code" v-html="qrCodeSvg" />
                        <p v-else class="security-text">
                            {{ twoFactorEnabled ? 'O QR code fica disponivel assim que a 2FA e ativada.' : 'Ative a 2FA para gerar o QR code.' }}
                        </p>

                        <div v-if="recoveryCodes.length" class="recovery-codes">
                            <div class="recovery-codes__header">
                                <p class="security-label">Recovery codes</p>
                                <Button variant="outline" size="sm" @click="copyRecoveryCodes">Copiar</Button>
                            </div>
                            <ul>
                                <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<style scoped>
.profile-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-hero {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1rem;
    align-items: center;
    padding: 1.5rem;
    border-radius: 24px;
    background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
    color: #fff;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

.profile-hero__avatar {
    display: grid;
    place-items: center;
    width: 4rem;
    height: 4rem;
    border-radius: 1.25rem;
    background: rgba(255, 255, 255, 0.14);
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.profile-hero__content h1 {
    margin: 0.25rem 0;
    font-size: clamp(1.5rem, 2vw, 2.2rem);
    line-height: 1.1;
}

.profile-hero__content p,
.profile-hero__hint,
.security-text {
    margin: 0;
    color: rgba(255, 255, 255, 0.78);
}

.profile-kicker,
.security-label {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.62);
}

.profile-hero__status {
    max-width: 18rem;
    justify-self: end;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    background: rgba(255, 255, 255, 0.12);
}

.status-pill--success {
    background: rgba(34, 197, 94, 0.18);
}

.status-pill--warning {
    background: rgba(251, 191, 36, 0.18);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

.profile-form {
    display: grid;
    gap: 1rem;
}

.field-group {
    display: grid;
    gap: 0.4rem;
}

.field-group--inline {
    margin-top: 1rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.25rem;
}

.security-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
    gap: 1.5rem;
}

.security-panel {
    display: grid;
    gap: 0.9rem;
    padding: 1rem;
    border-radius: 18px;
    background: rgba(15, 23, 42, 0.03);
}

.security-panel--soft {
    background: rgba(37, 99, 235, 0.06);
}

.security-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--color-text-main, #0f172a);
}

.security-text {
    color: var(--color-text-sub, #475569);
}

.security-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.qr-code {
    display: grid;
    justify-items: start;
    padding: 0.75rem;
    border-radius: 16px;
    background: #fff;
    overflow: hidden;
}

.qr-code :deep(svg) {
    width: 100%;
    max-width: 13rem;
    height: auto;
}

.recovery-codes {
    display: grid;
    gap: 0.75rem;
}

.recovery-codes__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.recovery-codes ul {
    display: grid;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.recovery-codes li {
    padding: 0.65rem 0.8rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(148, 163, 184, 0.24);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', monospace;
    font-size: 0.82rem;
    letter-spacing: 0.08em;
}

@media (max-width: 1024px) {

    .profile-hero,
    .profile-grid,
    .security-layout {
        grid-template-columns: 1fr;
    }

    .profile-hero__status {
        max-width: none;
        justify-self: start;
    }
}

@media (max-width: 640px) {
    .profile-hero {
        padding: 1.25rem;
    }

    .form-actions,
    .security-actions {
        flex-direction: column;
    }

    .security-actions :deep(button),
    .form-actions :deep(button) {
        width: 100%;
    }
}
</style>
