# InfraMail

An AWS infrastructure lab for a PHP mailing-address application backed by MySQL on RDS.
The project explores reverse proxies, separate application servers, and database access across network tiers.

## In the repository

- PHP application and Apache configuration under `app-web-db-config/`.
- Application startup scripts under `appservers-startup-scripts/`.
- Reverse-proxy configuration under `webservers-reverse-proxy-config/`.
- Architecture and subnet diagrams in the repository root.

## Explore the design

Start with the [architecture diagram](prod-env-project-architecture.png) and [database configuration notes](appservers-database-config/app-database-config.md).
The scripts expect manually configured AWS resources. They are not a complete infrastructure-as-code deployment.
