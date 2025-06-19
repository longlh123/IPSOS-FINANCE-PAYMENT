# Dockerfile for React
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package.json and install dependencies
COPY package*.json ./
RUN npm install

# Copy rest of the code
COPY . .

# Build the React app for production
RUN npm run build

# Expose the port your app will run on
EXPOSE 3000

# Define the command to run the app
CMD ["npm", "start"]