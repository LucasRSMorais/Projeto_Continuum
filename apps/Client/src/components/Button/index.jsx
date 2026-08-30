import { MyButton } from './styles' 
import React from 'react'

function Button({ Text, onClick, Type = 'button' }) {
  return (
    <div>
      <MyButton type={Type} onClick={onClick}>
        {Text}
      </MyButton>
    </div>
  );
}

export default Button;