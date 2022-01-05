# pptx2img-server
Dieses Dokument beschreibt, wie ein Ubuntu-Server so eingerichtet wird, dass dieser per HTTP-Request PPTX2-Dateien in JPEG oder PNG konvertiert.

## Ubuntu 20.04 LTS
Im folgenden wird die Einrichtung innerhalb einer frischen Ubuntu 20.04 Instanz beschrieben.

### Nginx
#### Installation
```
sudo apt update
sudo apt install nginx
```

### PHP
#### Installation
```
# Repository von Ondřej Surý hinzufügen
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php

# PHP 8.1 installieren
sudo apt install php8.1-fpm
```

#### Default-Config von Nginx öffnen
```
sudo nano /etc/nginx/sites-available/default
```

#### Index ergänzen
```
index index.html index.htm index.nginx-debian.html;
# wird zu 
index index.php index.html index.htm index.nginx-debian.html;
```

#### Location mit PHP-Endung im server-Block hinzufügen
```
location ~ \.php$ {
   include snippets/fastcgi-php.conf;
   fastcgi_pass unix:/run/php/php8.1-fpm.sock;
}
```

#### Speichern, schließen und Nginx neu starten
```
sudo systemctl restart nginx
```


### Let's Encrypt zertifikat einrichten (optional)

#### Default-Config von Nginx öffnen
```
sudo nano /etc/nginx/sites-available/default
```

#### server_name an Hostnamen angleichen, über den der Server erreichbar ist (www.servername.de anpassen!)
```
server_name www.servername.de;
```

#### Speichern, schließen und Nginx neu starten
```
sudo systemctl restart nginx
```

#### Certbot für Nginx installieren
```
sudo apt install certbot python3-certbot-nginx
```

#### Zertifikat für Domain ausstellen (www.servername.de anpassen!)
```
sudo certbot --nginx -d www.servername.de
```
