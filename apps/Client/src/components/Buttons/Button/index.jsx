import { MyButton } from './styles';

// Componente reutilizável de botão.
// Ele recebe o texto por 'Text' ou 'children' e aplica o comportamento de clique informado.
export function Button({ Text, onClick, Type, type, children, disabled = false, variant = 'primary' }) {
  const buttonType = Type ?? type ?? 'button';

  return (
    <MyButton
      type={buttonType}
      onClick={onClick}
      disabled={disabled}
      $variant={variant}
    >
      {Text ?? children}
    </MyButton>
  );
}

export default Button;