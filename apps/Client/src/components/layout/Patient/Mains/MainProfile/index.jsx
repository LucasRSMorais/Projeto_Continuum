import { useState } from 'react';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import Select from '@mui/material/Select';
import { useAuth } from '../../../../../services/utils/auth';
import { ActionButtons, CardContainer, SubCardContainer, SubCardHeader, DataCard, ProfileForm, ProfileMessage, Title, SubTitle } from './styles';
import { Button } from '../../../../../components/Buttons/Button';
import MeuInput from '../../../../Forms/TextField';
import { buildApiUrl } from '../../../../../config/api';

function Main() {
    
const formatTelefone = (value) => {
    const numeros = value.replace(/\D/g, '').slice(0, 11);

    if (numeros.length <= 2) {
        return numeros;
    }

    if (numeros.length <= 7) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
    }

    return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`;
};


const mascararTelefone = (telefone) => {
    if (!telefone) return 'Não informado';

    const numeros = String(telefone).replace(/\D/g, '');

    if (numeros.length < 4) return 'Não informado';

    const ddd = numeros.length >= 10
        ? numeros.slice(0, 2)
        : '';

    const finais = numeros.slice(-2);

    return ddd
        ? `(${ddd}) *****-**${finais}`
        : `*****${finais}`;
};


const mascararEmail = (email) => {
    if (!email || !email.includes('@')) {
        return 'Não informado';
    }   
    
    const [usuario, dominio] = email.split('@');
    const inicio = usuario.slice(0, 2);
    const mascara = '*'.repeat(Math.max(0, usuario.length - 2 , 3));

    return `${inicio}${mascara}@${dominio}`;
};

const mascararCPF = (cpf) => {
    if (!cpf) return 'Não informado';

    const numeros = String(cpf).replace(/\D/g, '');

    if (numeros.length !== 11) return 'CPF inválido';

    return `${numeros.slice(0, 3)}.***.***-${numeros.slice(-2)}`;
};



    const { user, loading, updateUser } = useAuth();
    const [isEditing, setIsEditing] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [formData, setFormData] = useState({ nome: '', email: '', telefone: '', sexo: '' });
    const [isEditingAddress, setIsEditingAddress] = useState(false);
    const [isSavingAddress, setIsSavingAddress] = useState(false);
    const [addressData, setAddressData] = useState({
        rua: '',
        cidade: '',
        estado: '',
        pais: '',
        cep: '',
    });
    const [message, setMessage] = useState('');
    const [messageIsError, setMessageIsError] = useState(false);
    const [addressMessage, setAddressMessage] = useState('');
    const [addressMessageIsError, setAddressMessageIsError] = useState(false);

    const startEditing = () => {
        setFormData({
            nome: user?.nome || '',
            email: user?.email || '',
            telefone: user?.telefone || '',
            sexo: user?.sexo || '',
        });
        setMessage('');
        setMessageIsError(false);
        setIsEditing(true);
    };

    const cancelEditing = () => {
        setIsEditing(false);
        setMessage('');
        setMessageIsError(false);
    };

    const handleFieldChange = (field) => (event) => {
        setFormData((current) => ({ ...current, [field]: event.target.value }));
    };

    const startEditingAddress = () => {
        setAddressData({
            rua: user?.endereco?.rua || '',
            cidade: user?.endereco?.cidade || '',
            estado: user?.endereco?.estado || '',
            pais: user?.endereco?.pais || 'Brasil',
            cep: user?.endereco?.cep || '',
        });
        setAddressMessage('');
        setAddressMessageIsError(false);
        setIsEditingAddress(true);
    };

    const cancelEditingAddress = () => {
        setIsEditingAddress(false);
        setAddressMessage('');
        setAddressMessageIsError(false);
    };

    const handleAddressFieldChange = (field) => (event) => {
        setAddressData((current) => ({ ...current, [field]: event.target.value }));
    };

    const handleSaveAddress = async (event) => {
        event.preventDefault();
        const updatedAddress = {
            rua: addressData.rua.trim(),
            cidade: addressData.cidade.trim(),
            estado: addressData.estado.trim(),
            pais: addressData.pais.trim(),
            cep: addressData.cep.replace(/\D/g, ''),
        };

        if (!updatedAddress.rua || !updatedAddress.cidade || !updatedAddress.estado || !updatedAddress.pais) {
            setAddressMessage('Preencha rua, cidade, estado e país.');
            setAddressMessageIsError(true);
            return;
        }
        if (updatedAddress.rua.length > 150 || updatedAddress.cidade.length > 100
            || updatedAddress.estado.length > 100 || updatedAddress.pais.length > 100) {
            setAddressMessage('Confira os limites de caracteres dos campos do endereço.');
            setAddressMessageIsError(true);
            return;
        }
        if (!/^\d{8}$/.test(updatedAddress.cep)) {
            setAddressMessage('Informe um CEP válido com 8 dígitos.');
            setAddressMessageIsError(true);
            return;
        }

        setIsSavingAddress(true);
        setAddressMessage('');
        setAddressMessageIsError(false);
        try {
            const response = await fetch(buildApiUrl('patients/atualizar_endereco_paciente.php'), {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedAddress),
            });
            const data = await response.json();

            if (!response.ok || !data.success || !data.endereco) {
                setAddressMessage(data.message || 'Não foi possível salvar o endereço.');
                setAddressMessageIsError(true);
                return;
            }

            updateUser({ endereco: data.endereco });
            setIsEditingAddress(false);
            setAddressMessage('Endereço atualizado com sucesso.');
        } catch (error) {
            console.error('Erro ao atualizar endereço:', error);
            setAddressMessage('Não foi possível salvar o endereço. Tente novamente.');
            setAddressMessageIsError(true);
        } finally {
            setIsSavingAddress(false);
        }
    };

    const handleSave = async (event) => {
        event.preventDefault();
        const updatedProfile = {
            nome: formData.nome.trim(),
            email: formData.email.trim().toLowerCase(),
            telefone: formData.telefone.trim(),
            sexo: formData.sexo,
        };
        const phoneDigits = updatedProfile.telefone.replace(/\D/g, '');

        if (!updatedProfile.nome) {
            setMessage('Informe seu nome completo.');
            setMessageIsError(true);
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(updatedProfile.email)) {
            setMessage('Informe um email válido.');
            setMessageIsError(true);
            return;
        }
        if (phoneDigits.length < 10 || phoneDigits.length > 15) {
            setMessage('Informe um telefone com 10 a 15 dígitos.');
            setMessageIsError(true);
            return;
        }
        if (!['masculino', 'feminino', 'nao_informado'].includes(updatedProfile.sexo)) {
            setMessage('Selecione uma opção para sexo.');
            setMessageIsError(true);
            return;
        }

        setIsSaving(true);
        setMessage('');
        setMessageIsError(false);
        try {
            const response = await fetch(buildApiUrl('patients/atualizar_perfil_paciente.php'), {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedProfile),
            });
            const data = await response.json();

            if (!response.ok || !data.success || !data.usuario) {
                setMessage(data.message || 'Não foi possível salvar as alterações.');
                setMessageIsError(true);
                return;
            }

            updateUser(data.usuario);
            setIsEditing(false);
            setMessage('Perfil atualizado com sucesso.');
        } catch (error) {
            console.error('Erro ao atualizar perfil:', error);
            setMessage('Não foi possível salvar as alterações. Tente novamente.');
            setMessageIsError(true);
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <CardContainer>
                <Title>Meu Perfil</Title>
            <SubCardContainer>
                <p><strong>Nome:</strong> {loading ? '...' : user?.nome || 'Usuário'}</p>
            </SubCardContainer>
            <div>
                <SubCardContainer>
                    <ProfileForm onSubmit={handleSave} noValidate>
                        <SubCardHeader>
                            <SubTitle>Informações Pessoais</SubTitle>
                            <ActionButtons>
                                {isEditing ? (
                                    <>
                                        <Button
                                            onClick={cancelEditing}
                                            disabled={isSaving}
                                            variant="secondary"
                                        >
                                            Cancelar
                                        </Button>
                                        <Button Type="submit" disabled={isSaving}>
                                            {isSaving ? 'Salvando...' : 'Salvar'}
                                        </Button>
                                    </>
                                ) : (
                                    <Button onClick={startEditing} disabled={loading}>
                                        Editar
                                    </Button>
                                )}
                            </ActionButtons>
                        </SubCardHeader>
                        <DataCard>
                            <MeuInput
                                id="input-nome"
                                label="Nome completo"
                                value={isEditing ? formData.nome : loading ? '...' : user?.nome || 'Não identificado'}
                                onChange={handleFieldChange('nome')}
                                required
                                disabled={!isEditing}
                                variant="standard"
                            />
                            <MeuInput
                                id="input-email"
                                label="Email"
                                type="email"
                                value={
                                    loading
                                        ? '...'
                                        : isEditing
                                            ? formData.email
                                            : mascararEmail(user?.email)
                                }
                                onChange={handleFieldChange('email')}
                                required
                                disabled={!isEditing}
                                variant="standard"
                            />
                            
                            <MeuInput
                                    id="input-telefone"
                                    label="Telefone"
                                    type="tel"
                                    value={
                                        loading
                                            ? '...'
                                            : isEditing
                                                ? formatTelefone(formData.telefone)
                                                : mascararTelefone(user?.telefone)
                                    }
                                    onChange={handleFieldChange('telefone')}
                                    required
                                    disabled={!isEditing}
                                    variant="standard"
                                />

                                     <MeuInput
                                id="input-cpf"
                                label="CPF"
                                value={

                                    loading
                                        ? '...'
                                        : isEditing
                                           ?user?.cpf || 'Não informado'
                                           : mascararCPF(user?.cpf)


                                }
                                disabled
                                variant="standard"
                            />

                            <MeuInput
                                id="input-data-nascimento"
                                label="Data de Nascimento"
                                value={loading ? '...' : user?.data_nascimento || 'Não informado'}
                                disabled
                                variant="standard"
                            />
                            <FormControl variant="standard" fullWidth required disabled={!isEditing}>
                                <InputLabel id="input-sexo-label">Sexo</InputLabel>
                                <Select
                                    labelId="input-sexo-label"
                                    id="input-sexo"
                                    value={isEditing ? formData.sexo : user?.sexo || ''}
                                    onChange={handleFieldChange('sexo')}
                                    label="Sexo"
                                >
                                    <MenuItem value="" disabled>Não informado</MenuItem>
                                    <MenuItem value="masculino">Masculino</MenuItem>
                                    <MenuItem value="feminino">Feminino</MenuItem>
                                    <MenuItem value="nao_informado">Prefiro não informar</MenuItem>
                                </Select>
                            </FormControl>
                        </DataCard>
                        {message && (
                            <ProfileMessage $error={messageIsError} role={messageIsError ? 'alert' : 'status'}>
                                {message}
                            </ProfileMessage>
                        )}
                    </ProfileForm>
                </SubCardContainer>
                <SubCardContainer>
                    <ProfileForm onSubmit={handleSaveAddress} noValidate>
                        <SubCardHeader>
                            <SubTitle>Endereço</SubTitle>
                            <ActionButtons>
                                {isEditingAddress ? (
                                    <>
                                        <Button
                                            onClick={cancelEditingAddress}
                                            disabled={isSavingAddress}
                                            variant="secondary"
                                        >
                                            Cancelar
                                        </Button>
                                        <Button Type="submit" disabled={isSavingAddress}>
                                            {isSavingAddress ? 'Salvando...' : 'Salvar'}
                                        </Button>
                                    </>
                                ) : (
                                    <Button onClick={startEditingAddress} disabled={loading}>
                                        Editar
                                    </Button>
                                )}
                            </ActionButtons>
                        </SubCardHeader>
                    <DataCard>
                        <MeuInput
                            id="input-rua"
                            label="Rua"
                            value={isEditingAddress ? addressData.rua : loading ? '...' : user?.endereco?.rua || 'Não informado'}
                            onChange={handleAddressFieldChange('rua')}
                            required
                            disabled={!isEditingAddress}
                            variant="standard"
                        />
                        <MeuInput
                            id="input-cidade"
                            label="Cidade"
                            value={isEditingAddress ? addressData.cidade : loading ? '...' : user?.endereco?.cidade || 'Não informado'}
                            onChange={handleAddressFieldChange('cidade')}
                            required
                            disabled={!isEditingAddress}
                            variant="standard"
                        />
                        <MeuInput
                            id="input-estado"
                            label="Estado"
                            value={isEditingAddress ? addressData.estado : loading ? '...' : user?.endereco?.estado || 'Não informado'}
                            onChange={handleAddressFieldChange('estado')}
                            required
                            disabled={!isEditingAddress}
                            variant="standard"
                        />
                        <MeuInput
                            id="input-pais"
                            label="País"
                            value={isEditingAddress ? addressData.pais : loading ? '...' : user?.endereco?.pais || 'Não informado'}
                            onChange={handleAddressFieldChange('pais')}
                            required
                            disabled={!isEditingAddress}
                            variant="standard"
                        />
                        <MeuInput
                            id="input-cep"
                            label="CEP"
                            value={isEditingAddress ? addressData.cep : loading ? '...' : user?.endereco?.cep || 'Não informado'}
                            onChange={handleAddressFieldChange('cep')}
                            required
                            disabled={!isEditingAddress}
                            variant="standard"
                        />
                    </DataCard>
                        {addressMessage && (
                            <ProfileMessage $error={addressMessageIsError} role={addressMessageIsError ? 'alert' : 'status'}>
                                {addressMessage}
                            </ProfileMessage>
                        )}
                    </ProfileForm>
                </SubCardContainer>
            </div>
        </CardContainer>
    );
}

export default Main;