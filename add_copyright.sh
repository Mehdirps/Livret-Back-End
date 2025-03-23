#!/bin/bash

# Texte à ajouter
header="/**
 * Copyright (c) 2025 Mehdi Raposo
 * Ce fichier fait partie du projet Heberginfos.
 *
 * Ce fichier, ainsi que tout le code et les ressources qu'il contient,
 * est protégé par le droit d'auteur. Toute utilisation, modification,
 * distribution ou reproduction non autorisée est strictement interdite
 * sans une autorisation écrite préalable de Mehdi Raposo.
 */"

# Extensions des fichiers à modifier
extensions=("*.js")

# Parcourir tous les fichiers correspondant aux extensions
for ext in "${extensions[@]}"; do
  for file in $(find . -type f -name "$ext"); do
    if ! grep -q "Copyright (c)" "$file"; then
      echo "Ajout de l'en-tête au fichier : $file"
      echo -e "$header\n$(cat "$file")" > "$file"
    fi
  done
done