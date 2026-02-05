import { title } from "process";
import { useState } from "react";

interface DialogOptions {
  title?: string;
  message?: string;
  onConfirm?: () => void;
  onClose?: () => void;
  showConfirmButton?: boolean;
}

const useDialog = () => {
  const [open, setOpen] = useState<boolean>(false);
  const [ options, setOptions ] = useState<DialogOptions>({});

  const openDialog = (options: DialogOptions = {}): void => {
    setOptions({
      showConfirmButton: false,
      ...options
    });
    setOpen(true);
  };

  const closeDialog = (): void => {
    if(options.onClose) options.onClose(); 
    setOpen(false);
  };

  const confirmDialog = (): void => {
    if(options.onConfirm) options.onConfirm();
    closeDialog()
  }

  return { 
    open, 
    title: options.title || "Xác nhận hành động",
    message: options.message || "Bạn có chắc chắn muốn thực hiện thao tác này?",
    showConfirmButton: options.showConfirmButton || false,
    openDialog, 
    closeDialog,
    confirmDialog
  };
};

export default useDialog;
