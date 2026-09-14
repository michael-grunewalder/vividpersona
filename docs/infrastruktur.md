Hier ist ein vollständiger, strukturierter Leitfaden, dem Sie Schritt für Schritt folgen können, um Ihre sichere Infrastruktur mit Coolify und NetBird aufzubauen.
------------------------------
## 📖 Leitfaden: Sichere Server-Infrastruktur mit Coolify & NetBird
Dieses Dokument beschreibt die Einrichtung von 1 Coolify-Hauptserver (Manager) und 1–2 Anwendungs-Servern (Worker). Am Ende sind alle Server im öffentlichen Internet unsichtbar (außer Port 80/443), kommunizieren untereinander verschlüsselt über NetBird und Sie können nur per VPN auf die Admin-Tools zugreifen.
------------------------------
## 🛠️ Vorbereitung & Voraussetzungen

* Server: Mindestens 2 Linux-Server (z. B. Ubuntu 22.04 LTS oder 24.04 LTS) mit öffentlicher IP.
* DNS: Eine eigene Domain (z. B. ihredomain.de), deren Wildcard-Einträge (*.ihredomain.de) auf die IP des Coolify-Hauptservers zeigen.
* NetBird-Konto: Ein Account auf netbird.io (oder eine eigene selbstgehostete NetBird-Instanz).

------------------------------
## 🟩 Schritt 1: NetBird-Netzwerk vorbereiten
Bevor wir die Server absichern, richten wir das VPN-Netzwerk ein, damit wir uns nicht selbst aussperren.

   1. Loggen Sie sich im NetBird Admin Panel ein.
   2. Gehen Sie auf Setup Keys und klicken Sie auf Create Setup Key.
   3. Wählen Sie folgende Einstellungen:
   * Name: Coolify-Infrastruktur
      * Type: Reusable (wiederverwendbar, da wir mehrere Server hinzufügen).
      * Expires in: 1 Tag (reicht für das Setup).
   4. Kopieren Sie den generierten Key (z. B. A1B2C3D4-...) und speichern Sie ihn temporär.

------------------------------
## 🟨 Schritt 2: NetBird auf allen Servern installieren
Führen Sie diese Schritte nacheinander auf allen Servern (Coolify-Manager + Worker) via SSH über deren aktuelle öffentliche IP aus.

   1. NetBird Client installieren:
   
   curl -FSsl https://netbird.io | sh
   
   2. Server mit dem Netzwerk verbinden (Setup Key einsetzen):
   
   netbird up --setup-key IHR_KOPIERTER_SETUP_KEY
   
   3. Verbindung prüfen:
   
   netbird status
   
   Notieren Sie sich die zugewiesene NetBird-IP (z. B. 100.64.0.5) und den Domainnamen (z. B. coolify-server.netbird.cloud). Alternativ sehen Sie diese nun im NetBird Web-Dashboard.

------------------------------
## 🟧 Schritt 3: Firewall (UFW) restriktiv konfigurieren
Jetzt schließen wir die Server für die Öffentlichkeit und erlauben Zugriffe nur noch über NetBird. Das NetBird-Netzwerk-Interface heißt unter Linux standardmäßig wt0.
Führen Sie diese Befehle auf ALLEN Servern aus:

   1. Standardregeln festlegen (Alles eingehende blockieren, ausgehend erlauben):
   
   ufw default deny incoming
   ufw default allow outgoing
   
   2. Öffentliche Ports erlauben (NUR für Web-Traffic):
   
   ufw allow 80/tcp
   ufw allow 443/tcp
   
   3. Volle Kommunikation im NetBird-Netzwerk erlauben:
   Hierdurch darf jeder Rechner, der im VPN ist, auf alle Ports dieses Servers zugreifen (inkl. SSH und Mailpit).
   
   ufw allow in on wt0
   
   4. Firewall aktivieren:
   
   ufw enable
   
   5. Status überprüfen:
   
   ufw status verbose
   
   
Ab jetzt ist SSH über die öffentliche IP blockiert. Verbinden Sie Ihren eigenen Laptop/PC ebenfalls mit NetBird, um fortan via ssh root@<netbird-ip-oder-dns> auf die Server zuzugreifen.
------------------------------
## 🟦 Schritt 4: Coolify (Manager) installieren
Führen Sie diesen Schritt nur auf dem Hauptserver aus. Nutzen Sie dafür bereits die NetBird-Verbindung.

   1. Coolify Installations-Skript starten:
   
   curl -fsSL https://coollabs.io | bash
   
   2. Einrichtung im Browser:
   * Öffnen Sie https://<Ihre-NetBird-Server-Domain>:3000 oder http://<NetBird-IP>:3000 an Ihrem Laptop.
      * Erstellen Sie das Admin-Konto.
   
------------------------------
## 🟪 Schritt 5: Anwendungs-Server (Worker) in Coolify einbinden
Damit Coolify Anwendungen auf den anderen Servern starten kann, verbinden wir sie nun über das sichere NetBird-Netzwerk.

   1. SSH-Key in Coolify kopieren:
   * Gehen Sie in Coolify auf Keys und nutzen Sie den standardmäßigen id.rsa-Schlüssel oder erstellen Sie einen neuen. Kopieren Sie den Public Key (öffentlichen Schlüssel).
   2. Public Key auf dem Worker-Server hinterlegen:
   * Loggen Sie sich auf dem Worker-Server ein und fügen Sie den Schlüssel hinzu:
      
      echo "HIER_DEN_PUBLIC_KEY_EINFÜGEN" >> ~/.ssh/authorized_keys
      chmod 600 ~/.ssh/authorized_keys
      
      3. Server in Coolify hinzufügen:
   * Gehen Sie in Coolify zu Servers -> Add New Server.
      * IP Address / Domain: Tragen Sie hier den NetBird-Domainnamen des Workers ein (z. B. worker-1.netbird.cloud).
      * Port: 22
      * User: root
      * Wählen Sie den passenden SSH-Key aus und klicken Sie auf Save Server und danach auf Validate.
      * Da UFW auf dem Worker Port 22 über wt0 erlaubt, wird die Verbindung sofort erfolgreich hergestellt.
   
------------------------------
## 🛟 Schritt 6: Interne Tools (z. B. Mailpit) absichern
Wenn Sie in Coolify Anwendungen wie Mailpit oder Admin-Dashboards erstellen, die nicht über Port 80/443 öffentlich erreichbar sein sollen, gehen Sie wie folgt vor:

   1. Erstellen Sie den Service (z. B. Mailpit) in Coolify.
   2. Wichtig: Nutzen Sie für diesen Service keine öffentliche Domain in den Coolify-Einstellungen.
   3. Stellen Sie in den Docker-Einstellungen des Services sicher, dass die Ports nicht nach außen geöffnet werden, oder binden Sie das Tool über den Coolify-internen Proxy so ein, dass es nur auf die interne NetBird-IP lauscht.
   4. Alternativ: Sie können im NetBird Admin-Panel unter Access Control genaue Richtlinien (Policies) definieren, welcher Benutzer (z. B. nur Ihr Laptop) auf den Port von Mailpit zugreifen darf.

------------------------------
## 🔒 Überprüfen Sie Ihr Setup

* Versuchen Sie, sich ohne aktives NetBird auf Ihrem Laptop per SSH auf einen der Server zu verbinden. -> Muss fehlschlagen (Timeout).
* Aktivieren Sie NetBird auf Ihrem Laptop und pingen Sie coolify-server.netbird.cloud. -> Muss funktionieren.
* Rufen Sie die Coolify-Oberfläche über die NetBird-Adresse auf. -> Muss funktionieren.

Soll ich Ihnen ein konkretes Docker-Compose-Beispiel für Mailpit in Coolify zeigen, das so konfiguriert ist, dass es ausschließlich über die NetBird-IP erreichbar bleibt?

