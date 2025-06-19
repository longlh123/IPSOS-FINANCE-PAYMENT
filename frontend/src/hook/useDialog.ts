import { useState } from "react";

const useDialog = () => {
  const [open, setOpen] = useState<boolean>(false);

  const openDialog = (): void => {
    setOpen(true);
  };

  const closeDialog = (): void => {
    setOpen(false);
  };

  return { open, openDialog, closeDialog };
};

export default useDialog;
