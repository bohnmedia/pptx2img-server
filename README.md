# pptx2img-server

Dieses Dokument beschreibt, wie ein Ubuntu-Server so eingerichtet wird, dass dieser per HTTP-Request PPTX2-Dateien in JPEG oder PNG konvertiert.

## Ubuntu 20.04 LTS

Im folgenden wird die Einrichtung innerhalb einer frischen Ubuntu 20.04 Instanz beschrieben.

### Nginx

Installation

```
sudo apt update
sudo apt install nginx
```

Limits in nginx.conf erhöhen

```
sudo nano /etc/nginx/nginx.conf

# Folgende Werte in http-Block nach "Basic Settings" ergänzen
client_max_body_size 64M;
fastcgi_read_timeout 300;
```

Speichern, schließen und Nginx neu starten

```
sudo systemctl restart nginx
```

www-data das Schreiben in /var/www/html erlauben

```
chown www-data:www-data /var/www/html
```

### PHP

Installation

```
# Repository von Ondřej Surý hinzufügen
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php

# PHP 8.1 installieren
sudo apt install php8.1-fpm
```

Uploadlimit und Laufzeit erhöhern

```
# php.ini öffnen
sudo nano /etc/php/8.1/fpm/php.ini

# Folgende Werte anpassen
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300
```

Speichern, schließen und PHP neu starten

```
sudo systemctl restart php8.1-fpm
```

Default-Config von Nginx öffnen

```
sudo nano /etc/nginx/sites-available/default
```

index.php ergänzen

```
index index.html index.htm index.nginx-debian.html;
# wird zu
index index.php index.html index.htm index.nginx-debian.html;
```

Location mit PHP-Endung im server-Block hinzufügen
Ordner und Dateien, die mit einem Punkt beginnen, blocken

```
location ~ \.php$ {
   include snippets/fastcgi-php.conf;
   fastcgi_pass unix:/run/php/php8.1-fpm.sock;
}

location ~ /\. {
    deny all;
}
```

Speichern, schließen und Nginx neu starten

```
sudo systemctl restart nginx
```

### Let's Encrypt Zertifikat (optional)

Default-Config von Nginx öffnen

```
sudo nano /etc/nginx/sites-available/default
```

server_name an Hostnamen angleichen, über den der Server erreichbar ist (www.servername.de anpassen!)

```
server_name www.servername.de;
```

Speichern, schließen und Nginx neu starten

```
sudo systemctl restart nginx
```

Certbot für Nginx installieren

```
sudo apt install certbot python3-certbot-nginx
```

Zertifikat für Domain ausstellen (www.servername.de anpassen!)

```
sudo certbot --nginx -d www.servername.de
```

### Schnittstelle vor Fremdzugriff schützen (optional)

apache2-utils zur Erstellung der htpasswd Datei installieren

```
sudo apt-get install apache2-utils
```

htpasswd-Datei mit Benutzername und Passwort generieren (benutzername anpassen!)

```
sudo htpasswd -c /etc/apache2/.htpasswd benutzername
```

Default-Config von Nginx öffnen

```
sudo nano /etc/nginx/sites-available/default
```

"location ~ \.php$" um auth_basic und auth_basic_user_file ergänzen

```
location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    auth_basic "pptx2img";
    auth_basic_user_file /etc/apache2/.htpasswd;
}
```

Speichern, schließen und Nginx neu starten

```
sudo systemctl restart nginx
```

### LibreOffice

Installation inklusive Impress

```
sudo apt-get install libreoffice libreoffice-impress
```

### ImageMagick für PHP

Installation

```
sudo apt-get install php-imagick
```

Lesen von PDF-Dateien erlauben

```
# policy.xml öffnen
sudo nano /etc/ImageMagick-6/policy.xml

# Folgenden Eintrag ändern
<policy domain="coder" rights="none" pattern="PDF" />
# in
<policy domain="coder" rights="read" pattern="PDF" />
```

Speichern, schließen und PHP neu starten

```
sudo systemctl restart php8.1-fpm
```

### PHP-Script

In Web-Verzeichnis wechseln

```
cd /var/www/html
```

Inhalt löschen

```
rm *.*
```

[index.html](index.html) und [convert.php](convert.php) in /var/www/html kopieren oder das Repo in dieses Verzeichnis klonen.

### Test

Das Script kann auf zwei wegen getestet werden. Entweder per HTML-Formular, indem die Server-URL über den Browser aufgerufen wird, oder von einem anderen Server aus über das Testscript [testscript.php](testscript.php).

Bei Verwendung des Testscripts müssen die Zeilen 3 bis 8 entsprechend angepasst werden.
