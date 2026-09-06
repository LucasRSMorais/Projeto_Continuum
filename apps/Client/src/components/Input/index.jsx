import { MyInput } from './styles';

// Componente reutilizável para campos de texto, senha e e-mail.
// Ele apenas encapsula o input com o valor e a função que atualiza o estado do formulário.
function Input({ type, placeholder, value, onChange }) {
  return (
    <div>
      <MyInput
        value={value}
        onChange={onChange}
        type={type}
        placeholder={placeholder}
      />
    </div>
  );
}

export default Input;