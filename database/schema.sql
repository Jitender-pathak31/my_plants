-- Erstellen Sie die Datenbank, falls sie noch nicht existiert
-- CREATE DATABASE IF NOT EXISTS pflanzen_db;
-- USE pflanzen_db;

-- Tabelle 'pflanzen' erstellen
CREATE TABLE IF NOT EXISTS pflanzen (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        name VARCHAR(255) NOT NULL,
    kaufdatum DATE NOT NULL,
    standort VARCHAR(255) NOT NULL,
    bewaesserung_in_tage INT NOT NULL,
    gegossen DATETIME NULL DEFAULT NULL, -- Kann NULL sein
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Beispiel-Daten einfügen (optional)
INSERT INTO pflanzen (name, kaufdatum, standort, bewaesserung_in_tage, gegossen) VALUES
                                                                                     ('Monstera Deliciosa', '2023-05-10', 'Wohnzimmer', 7, '2024-07-10 14:30:00'),
                                                                                     ('Aloe Vera', '2022-11-20', 'Küche', 14, '2024-07-05 10:00:00'),
                                                                                     ('Ficus Lyrata', '2024-01-15', 'Schlafzimmer', 5, '2024-07-08 09:15:00');
