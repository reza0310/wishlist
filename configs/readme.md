# This folder contain various configurations files

Configurations:
- `nginx/` : Nginx server configurations
- `apache/` : Apache configurations
- `mariadb/` : MariaDB configuration
- `nftables/` : Firewall rules
- `services/` : SystemD services files

## Network ports mapping

Public ports:
```yaml
  80 : Main HTTP
 443 : Main HTTPs
2808 : Rest API HTTP
2443 :  Rest API HTTPs
```

Local/private ports:
```yaml
3306 : MariaDB
```
