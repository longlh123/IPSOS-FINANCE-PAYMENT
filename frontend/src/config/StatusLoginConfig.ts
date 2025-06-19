const StatusLoginConfig = [
  {
    statusCode: "00",
    response: {
      textHeader: "API key is not correct",
      textContent: "Validation failed",
    },
  },
  {
    statusCode: "01",
    response: {
      textHeader: "API key is not correct",
      textContent: "Validation failed",
    },
  },
  {
    statusCode: "02",
    response: {
      textHeader: "User permission",
      textContent: "User don't have permission to access this information",
    },
  },
  {
    statusCode: "03",
    response: {
      textHeader: "Bad request",
      textContent: "Missing data",
    },
  },
  {
    statusCode: "04",
    response: {
      textHeader: "API key is not correct",
      textContent: "Validation failed",
    },
  },
  // Add more responses as needed
];

export default StatusLoginConfig;
