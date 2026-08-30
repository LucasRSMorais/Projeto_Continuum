import { createContext, useContext, useEffect, useMemo, useState } from 'react';

// Este arquivo contém a lógica de autenticação
// Verifica se o usuário está logado com base no token no localStorage
const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);

    useEffect(() => {
        const userToken = localStorage.getItem('user_token');

        if (!userToken) {
            setUser(null);
            return;
        }

        try {
            const parsedToken = JSON.parse(userToken);
            const usersStorage = JSON.parse(localStorage.getItem('users_db') ?? '[]');
            const hasUser = usersStorage.find((user) => user.email === parsedToken.email);

            setUser(hasUser ? { email: hasUser.email } : null);
        } catch {
            localStorage.removeItem('user_token');
            setUser(null);
        }
    }, []);

    // Aqui verifica se o usuário existe no LocalStorage e se a senha está correta, caso positivo, cria um token e salva no LocalStorage
    const login = (email, password) => {
        const usersStorage = JSON.parse(localStorage.getItem('users_db') ?? '[]');
        const hasUser = usersStorage.find((user) => user.email === email);

        // Se o usuário não existir, retorna uma mensagem de erro
        if (!hasUser) {
            return 'Usuário não encontrado';
        }

        // Verifica se o e-mail e a senha estão corretos, caso positivo, cria um token e salva no LocalStorage
        // Capaz de verificar se um dos requisitos está correto e outro não, retornando uma mensagem de erro igual para segurança
        if (hasUser.email === email && hasUser.password === password) {
            const token = Math.random().toString(36).substring(2);
            const authData = { email, token };

            localStorage.setItem('user_token', JSON.stringify(authData));
            setUser({ email: hasUser.email });
            return null;
        } else {
            return 'E-mail ou senha incorreta!';
        }
    };

    // Aqui remove o token do LocalStorage e desloga o usuário
    const logout = () => {
        localStorage.removeItem('user_token');
        setUser(null);
    };

    const value = useMemo(
        () => ({
            user,
            login,
            logout,
            isAuthenticated: !!user,
        }),
        [user]
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};