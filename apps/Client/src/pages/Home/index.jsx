import { useAuth } from '../../services/utils/auth';

function Home() {
  const { user, logout } = useAuth();

  return (
    <div>
      <h1>Home</h1>
      <p>Bem-vindo, {user?.email || 'usuário'}!</p>
      <button type="button" onClick={logout}>Sair</button>
    </div>
  );
}

export default Home;
