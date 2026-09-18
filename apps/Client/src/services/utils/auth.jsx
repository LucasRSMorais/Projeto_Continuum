import {createContext, useContext, useEffect, useMemo, useState} from 'react';
import { buildApiUrl } from '../../config/api';

// Contexto de autenticação global do app.
// Ele guarda informações do usuário logado, o estado de carregamento
// e as funções de sessão para toda a aplicação.
export const AuthContext = createContext(null);
export const useAuth = () => {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error(
            'useAuth deve ser usado dentro de um AuthProvider'
        );
    }
    return context;
};

const API_URL = buildApiUrl('');

// Provider responsável por manter o estado de autenticação em toda a aplicação.
export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    // Consulta o backend para saber se o usuário já está autenticado.
    // Se a sessão for válida, salva os dados do usuário no estado local.
    // Isso permite que a aplicação saiba quem está logado em qualquer página.
    const checkSession = async () => {
        try {
            const response = await fetch(
                `${API_URL}/session.php`,{
                    method: 'GET',
                    credentials: 'include',
                }
            );

            const data = await response.json();
            if (response.ok && data.authenticated) {
                setUser(data.usuario);
            } else {
                setUser(null);
            }

        } catch (error) {
            console.error(
                'Erro ao verificar sessão:',
                error
            ); setUser(null);
        } finally {
            setLoading(false);
        }
    };

// Ao iniciar a aplicação, valida se já existe uma sessão ativa.
// Esse efeito executa automaticamente quando o componente é montado.
    useEffect(() => {checkSession();}, []);

    // Faz o logout no backend e limpa os dados do usuário no frontend.
    // O backend destrói a sessão e o estado local deixa o usuário como não autenticado.
    const logout = async () => {
    try {
        await fetch(`${API_URL}/logout.php`, {
            method: 'POST',
            credentials: 'include',
        });
    } catch (error) {
        console.error('Erro ao realizar logout:', error);
    } finally {
        setUser(null);
    }
};

    const value = useMemo(() => ({user, logout, isAuthenticated: !!user, loading, checkSession,}),[user, loading]
    );

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};