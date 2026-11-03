// Este arquivo implementa a autenticação global do frontend.
// O objetivo é manter a sessão do usuário em um único ponto e permitir
// que qualquer tela do sistema verifique se o visitante está logado.

import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { buildApiUrl } from '../../config/api';

// Cria o contexto compartilhado entre telas e componentes.
// Ele armazena o usuário autenticado e o status da sessão atual.
export const AuthContext = createContext(null);

/**
 * Hook para consumir o contexto de autenticação.
 * Evita que componentes acessem o estado sem o provider correto.
 */
export const useAuth = () => {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth deve ser usado dentro de um AuthProvider');
    }
    return context;
};

const API_URL = buildApiUrl('');

/**
 * Provider responsável por manter o estado da sessão em toda a aplicação.
 * Ele consulta o backend ao iniciar para confirmar se a sessão é válida.
 */
export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    /**
     * Verifica no backend se o usuário continua autenticado.
     * Se a sessão estiver valida, os dados do usuário são carregados no estado local.
     */
    const checkSession = async () => {
        try {
            const response = await fetch(`${API_URL}/session.php`, {
                method: 'GET',
                credentials: 'include',
            });

            const data = await response.json();
            if (response.ok && data.authenticated) {
                setUser(data.usuario);
            } else {
                setUser(null);
            }
        } catch (error) {
            console.error('Erro ao verificar sessão:', error);
            setUser(null);
        } finally {
            setLoading(false);
        }
    };

    // Quando a aplicação é montada, já dispara a validação da sessão ativa.
    useEffect(() => {
        checkSession();
    }, []);

    /**
     * Faz logout no backend e limpa o usuário do frontend.
     * Em seguida, a navegação é redirecionada para a tela de login.
     */
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

    const value = useMemo(
        () => ({ user, logout, isAuthenticated: !!user, loading, checkSession }),
        [user, loading]
    );

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};