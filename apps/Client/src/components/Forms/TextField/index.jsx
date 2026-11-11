import FormControl from '@mui/material/FormControl';
import FormHelperText from '@mui/material/FormHelperText';
import InputLabel from '@mui/material/InputLabel';
import Input from '@mui/material/Input';
import OutlinedInput from '@mui/material/OutlinedInput';
import FilledInput from '@mui/material/FilledInput';

export default function MeuInput({ 
  label, 
  value, 
  onChange, 
  id, 
  helperText, 
  error = false, 
  disabled = false, 
  required = false,
  type = 'text',
  variant = 'standard'
}) {
  let InputComponent = Input;
  if (variant === 'outlined') InputComponent = OutlinedInput;
  if (variant === 'filled') InputComponent = FilledInput;

  return (
    <FormControl 
      error={error} 
      disabled={disabled} 
      required={required}
      variant={variant}
      
      sx={{ width: '100%', minWidth: 0, m: 0 }}
    >
      <InputLabel htmlFor={id}>{label}</InputLabel>
      <InputComponent
        id={id}
        type={type}
        value={value}
        onChange={onChange}
        label={variant === 'outlined' ? label : undefined}
        aria-describedby={helperText ? `${id}-helper-text` : undefined}
      />
      {helperText && (
        <FormHelperText id={`${id}-helper-text`}>{helperText}</FormHelperText>
      )}
    </FormControl>
  );
}