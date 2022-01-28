#!/bin/bash

symfony console doctrine:schema:drop --force
symfony console doctrine:query:sql -q "TRUNCATE doctrine_migration_versions"
symfony console doctrine:migrations:migrate --no-interaction
symfony console doctrine:fixtures:load --no-interaction
