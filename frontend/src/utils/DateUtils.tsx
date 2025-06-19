// src/utils/dateUtils.ts

export const formatDate = (isoString: string): string => {
  const date = new Date(isoString);

  const pad = (num: number): string => num.toString().padStart(2, "0");

  return `${pad(date.getUTCDate())}/${pad(
    date.getUTCMonth() + 1
  )}/${date.getUTCFullYear()} `;
};
