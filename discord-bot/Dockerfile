FROM node:18-alpine

WORKDIR /usr/src/app

# Installation des dépendances de base
RUN apk add --no-cache python3 make g++

# Copie des fichiers package
COPY package*.json ./

# Installation des dépendances
RUN npm install

# Le dossier src sera monté comme volume
CMD [ "npm", "start" ]