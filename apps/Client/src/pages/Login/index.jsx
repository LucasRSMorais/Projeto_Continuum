import { Title } from './styles';
import Button from '../../components/Button';

function Login() {
  return (
    <div>
      <Title>Login</Title>
      <form>
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <Button type="submit">Login</Button>
      </form>
    </div>
  );
}

export default Login;