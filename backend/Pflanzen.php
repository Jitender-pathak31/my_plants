<?php
require_once 'Singleton_database.php';

class Pflanzen
{
    private int $id;
    private string $name;
    private ?DateTime $kaufdatum;
    private string $standort;
    private int $bewaesserung_in_tage;
    private ?DateTime $gegossen; // Nullable DateTime
    private $db;

    public function __construct(int $id = 0, string $name = '', ?DateTime $kaufdatum = null, string $standort = '', int $bewaesserung_in_tage = 0, ?DateTime $gegossen = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->kaufdatum = $kaufdatum;
        $this->standort = $standort;
        $this->bewaesserung_in_tage = $bewaesserung_in_tage;
        $this->gegossen = $gegossen; // Nullable DateTime
        $this->db = Singleton_database::getInstance()->getConnection();
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKaufdatum(): ?string
    {
        return $this->kaufdatum ? $this->kaufdatum->format('Y-m-d') : null; // Return formatted date or null
    }

    public function getStandort(): string
    {
        return $this->standort;
    }

    public function getBewaesserungInTage(): int
    {
        return $this->bewaesserung_in_tage;
    }

    public function getGegossen(): ?string
    {
        return $this->gegossen ? $this->gegossen->format('Y-m-d H:i:s') : null; // Return formatted date or null
    }

    // Setter methods (optional, mainly for internal use or if you load data into object)
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setKaufdatum(?DateTime $kaufdatum): void
    {
        $this->kaufdatum = $kaufdatum;
    }

    public function setStandort(string $standort): void
    {
        $this->standort = $standort;
    }

    public function setBewaesserungInTage(int $bewaesserung_in_tage): void
    {
        $this->bewaesserung_in_tage = $bewaesserung_in_tage;
    }

    public function setGegossen(?DateTime $gegossen): void
    {
        $this->gegossen = $gegossen;
    }


    // CRUD functions

    public function create(string $name, string $kaufdatum_str, string $standort, int $bewaesserung, ?string $gegossen_str): bool
    {
        $sql = "INSERT INTO pflanzen (name, kaufdatum, standort, bewaesserung_in_tage, gegossen)
                VALUES (:name, :kaufdatum, :standort, :bewaesserung_in_tage, :gegossen)";

        try {
            $stmt = $this->db->prepare($sql);

            $kaufdatum_obj = new DateTime($kaufdatum_str);
            $gegossen_obj = $gegossen_str ? new DateTime($gegossen_str) : null;

            $result = $stmt->execute([
                ':name' => $name,
                ':kaufdatum' => $kaufdatum_obj->format('Y-m-d'), // Datum ohne Zeit
                ':standort' => $standort,
                ':bewaesserung_in_tage' => $bewaesserung,
                ':gegossen' => $gegossen_obj ? $gegossen_obj->format('Y-m-d H:i:s') : null
            ]);

            if ($result) {
                $this->id = (int)$this->db->lastInsertId();
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error creating plant: " . $e->getMessage());
        } catch (Exception $e) { // For DateTime parsing errors
            error_log("Date parsing error: " . $e->getMessage());
        }

        return false;
    }


    public function read(): array
    {
        $sql = "SELECT * FROM pflanzen ORDER BY id DESC"; // Desc Order
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error reading plants: " . $e->getMessage());
            return [];
        }
    }


    public function update(int $id, array $data): bool
    {
        // Build SQL query dynamically based on provided data
        $setClauses = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $setClauses[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        if (isset($data['kaufdatum'])) {
            $setClauses[] = 'kaufdatum = :kaufdatum';
            try {
                $kaufdatum_obj = new DateTime($data['kaufdatum']);
                $params[':kaufdatum'] = $kaufdatum_obj->format('Y-m-d');
            } catch (Exception $e) {
                error_log("Date parsing error for kaufdatum in update: " . $e->getMessage());
                return false;
            }
        }
        if (isset($data['standort'])) {
            $setClauses[] = 'standort = :standort';
            $params[':standort'] = $data['standort'];
        }
        if (isset($data['bewaesserung_in_tage'])) {
            $setClauses[] = 'bewaesserung_in_tage = :bewaesserung_in_tage';
            $params[':bewaesserung_in_tage'] = (int)$data['bewaesserung_in_tage'];
        }
        // Special handling for 'gegossen' - can be null or a valid date string
        if (array_key_exists('gegossen', $data)) { // Using array_key_exists to handle explicit null
            $setClauses[] = 'gegossen = :gegossen';
            if ($data['gegossen'] === null || $data['gegossen'] === '') {
                $params[':gegossen'] = null;
            } else {
                try {
                    $gegossen_obj = new DateTime($data['gegossen']);
                    $params[':gegossen'] = $gegossen_obj->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    error_log("Date parsing error for gegossen in update: " . $e->getMessage());
                    return false;
                }
            }
        }

        if (empty($setClauses)) {
            return false; // No data to update
        }

        $sql = "UPDATE pflanzen SET " . implode(', ', $setClauses) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating plant: " . $e->getMessage());
            return false;
        }
    }


    public function delete(int $id): bool
    {
        $sql = "DELETE FROM pflanzen WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting plant: " . $e->getMessage());
            return false;
        }
    }
}


$test = new Pflanzen();

$test->update(1, ['standort' => 'Küche']);