import { useState, useEffect, useRef } from 'react';

export function useOutsideClick(initialValue = false) {
  const [isOpen, setIsOpen] = useState(initialValue);
  const ref = useRef(null);

  useEffect(() => {
    const handleClickOutside = (event) => {
      // Verifica se o clique ocorreu fora do elemento referenciado
      if (ref.current && !ref.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    // Adiciona o event listener quando o componente é montado
    document.addEventListener('mousedown', handleClickOutside);
    
    // Remove o event listener quando o componente é desmontado
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  return { isOpen, setIsOpen, ref };
}