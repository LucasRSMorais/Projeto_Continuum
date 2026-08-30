// Este hook é usado para acessar o contexto de autenticação em qualquer componente
// Ele fornece acesso ao usuário logado, bem como às funções de login e logout

import { useContext } from 'react';
import { AuthContext } from '../services/utils/auth';

export const useAuth = () => {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth deve ser usado dentro de um AuthProvider');
    }

    return context;
};