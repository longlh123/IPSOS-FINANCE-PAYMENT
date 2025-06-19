// src/contexts/InputContext.tsx

import React, { createContext, useState, useContext, ReactNode } from 'react';

interface InputContextProps {
  inputs: { [key: string]: string };
  setInputValue: (key: string, value: string) => void;
}

const InputContext = createContext<InputContextProps | undefined>(undefined);

export const InputProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [inputs, setInputs] = useState<{ [key: string]: string }>({});

  const setInputValue = (key: string, value: string) => {
    setInputs((prevInputs) => ({
      ...prevInputs,
      [key]: value,
    }));
  };

  return (
    <InputContext.Provider value={{ inputs, setInputValue }}>
      {children}
    </InputContext.Provider>
  );
};

export const useInputContext = (): InputContextProps => {
  const context = useContext(InputContext);
  if (!context) {
    throw new Error('useInputContext must be used within an InputProvider');
  }
  return context;
};
