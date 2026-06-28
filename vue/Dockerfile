FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install --include=dev && npm rebuild esbuild
RUN npm list chart.js || npm install chart.js --save

COPY . .
EXPOSE 5174
CMD sh -c "npm install --include=dev && npm rebuild esbuild && npm run dev -- --host 0.0.0.0"