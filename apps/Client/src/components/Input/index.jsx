import React from 'react'

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