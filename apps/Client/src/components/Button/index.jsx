import { MyButton } from './styles';

export function Button({ Text, onClick, Type, type, children }) {
  const buttonType = Type ?? type ?? 'button';

  return (
    <MyButton type={buttonType} onClick={onClick}>
      {Text ?? children}
    </MyButton>
  );
}

export default Button;