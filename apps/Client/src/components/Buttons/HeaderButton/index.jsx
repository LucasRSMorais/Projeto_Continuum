import { MyHeaderButton } from './styles';

// Link do React Router permite reutilizar o botão sem recarregar a aplicação.
function HeaderButton({ to, children, Text, onClick }) {
  return (
    <MyHeaderButton to={to} onClick={onClick}>
      {Text ?? children}
    </MyHeaderButton>
  );
}

// Exporta das duas formas para permitir importações nomeadas ou padrão.
export { HeaderButton };
export default HeaderButton;