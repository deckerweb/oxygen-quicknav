# Fragen nach Themen

[English](FAQ.md)

## Einstieg

### Wählt QuickNav den passenden Oxygen automatisch?

Ja. Der aktive Builder bestimmt Datenabfragen, Gruppen, Rechte, Einstellungen und Bearbeitungs-URLs. Sind beide Integrationen vorhanden, erhalten sie getrennte Untermenüs. Dadurch werden ansonsten inkompatible Hersteller-Plugins nicht gleichzeitig betreibbar.

### Warum gibt es in Classic keine getrennten Header und Footer?

Classic speichert Templates und wiederverwendbare Teile in ct_template. QuickNav folgt dieser Struktur, statt Header oder Footer anhand des Namens zu vermuten.

## Alltag & Einstellungen

### Können Redakteure QuickNav verwenden?

Ein Administrator kann die erforderliche Toolbar-Berechtigung ändern. Jedes Ziel prüft weiterhin WordPress- und Oxygen-Rechte; QuickNav-Sichtbarkeit erteilt keinen Builder-Zugriff.

### Kann ich Entwürfe, Beiträge oder Produkte anzeigen?

Ja. Wähle öffentliche Inhaltstypen und optional unveröffentlichte Inhalte in den Einstellungen. Es erscheinen nur bearbeitbare Inhalte mit Builder-Daten. Produkte benötigen den registrierten Inhaltstyp und passende Benutzerrechte.

### Teilt eine Netzwerkaktivierung meine Einstellungen?

Nein. Standardwerte und persönliche Vorlieben gelten pro Website. Neue Websites verwenden automatisch die Standardwerte. Die gemeinsame Library verwaltet ihren eigenen Website-/Netzwerk-Geltungsbereich.

## Kompatibilität & Daten

### Was passiert bei einer Deinstallation mit meinen Daten?

QuickNav-Einstellungen bleiben standardmäßig erhalten. Die optionale Löschung entfernt nur auf den zustimmenden Websites deren QuickNav-Einstellungen und persönliche Vorlieben. Oxygen-Inhalte bleiben erhalten. Library-Daten bleiben bestehen, solange ein anderer eingebetteter Host installiert ist.

### Verbindet sich das Plugin mit externen Diensten?

Der Updater prüft dieses öffentliche GitHub-Repository im WordPress-Updateablauf; GitHub erhält übliche Anfrage-Metadaten. Kein Token ist nötig, Telemetrie kommt nicht hinzu. Der optionale Onlinekatalog der Library ist anfangs aus; seine Aktivierung erlaubt die dokumentierten GitHub-Katalog- und Paketabfragen.
