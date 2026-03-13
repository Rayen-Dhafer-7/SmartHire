FROM node:20-alpine

WORKDIR /app

COPY package*.json ./

# Delete node_modules and reinstall fresh inside Linux container
# This fixes esbuild binary mismatch (Windows vs Linux)
RUN npm install && npm rebuild esbuild

COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
