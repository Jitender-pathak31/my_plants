// Define the interface for a Plant object
interface Pflanze {
    id: number;
    name: string;
    kaufdatum: string; // YYYY-MM-DD
    standort: string;
    bewaesserung_in_tage: number;
    gegossen: string | null;
    created_at?: string;
    updated_at?: string;
}

// --- DOM Elements ---
const plantForm = document.getElementById('plant-form') as HTMLFormElement;
const plantIdInput = document.getElementById('plant-id') as HTMLInputElement;
const nameInput = document.getElementById('name') as HTMLInputElement;
const kaufdatumInput = document.getElementById('kaufdatum') as HTMLInputElement;
const standortInput = document.getElementById('standort') as HTMLInputElement;
const bewaesserungInput = document.getElementById('bewaesserung_in_tage') as HTMLInputElement;
const gegossenInput = document.getElementById('gegossen') as HTMLInputElement;

const submitBtn = document.getElementById('submit-btn') as HTMLButtonElement;
const cancelEditBtn = document.getElementById('cancel-edit-btn') as HTMLButtonElement;
const formTitle = document.getElementById('form-title') as HTMLElement;
const formMessage = document.getElementById('form-message') as HTMLElement;
const tableMessage = document.getElementById('table-message') as HTMLElement;
const plantsTableBody = document.querySelector('#plants-table tbody') as HTMLTableSectionElement;

const API_BASE_URL = 'http://localhost/pflanzen_app/backend/api.php'; // Path

// --- Helper Functions ---

function showMessage(element: HTMLElement, message: string, isSuccess: boolean): void {
    element.textContent = message;
    element.classList.remove('hidden', 'message-success', 'message-error');
    element.classList.add(isSuccess ? 'message-success' : 'message-error');
    setTimeout(() => {
        element.classList.add('hidden');
    }, 5000); // Hide after 5 seconds
}

function resetForm(): void {
    plantForm.reset();
    plantIdInput.value = '';
    formTitle.textContent = 'Neue Pflanze hinzufügen';
    submitBtn.textContent = 'Hinzufügen';
    submitBtn.classList.remove('btn-info');
    submitBtn.classList.add('btn-primary');
    cancelEditBtn.classList.add('hidden');
}

/**
 * Populates the form with plant data for editing.
 * @param plant The plant object to load into the form.
 */
function populateFormForEdit(plant: Pflanze): void {
    plantIdInput.value = plant.id.toString();
    nameInput.value = plant.name;
    kaufdatumInput.value = plant.kaufdatum; // YYYY-MM-DD
    standortInput.value = plant.standort;
    bewaesserungInput.value = plant.bewaesserung_in_tage.toString();

    // Format gegossen for datetime-local input
    if (plant.gegossen) {
        const date = new Date(plant.gegossen);
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        gegossenInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    } else {
        gegossenInput.value = '';
    }

    formTitle.textContent = `Pflanze bearbeiten (ID: ${plant.id})`;
    submitBtn.textContent = 'Aktualisieren';
    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-info');
    cancelEditBtn.classList.remove('hidden');
}

/**
 * Renders the plants table with the given data.
 * @param plants An array of plant objects.
 */
function renderPlantsTable(plants: Pflanze[]): void {
    plantsTableBody.innerHTML = ''; // Clears existing rows
    if (plants.length === 0) {
        tableMessage.textContent = 'Keine Pflanzen gefunden. Fügen Sie eine neue Pflanze hinzu!';
        tableMessage.classList.remove('hidden', 'message-error');
        tableMessage.classList.add('message-success');
        return;
    } else {
        tableMessage.classList.add('hidden');
    }

    plants.forEach(plant => {
        const row = plantsTableBody.insertRow();
        row.innerHTML = `
            <td>${plant.id}</td>
            <td>${plant.name}</td>
            <td>${plant.kaufdatum}</td>
            <td>${plant.standort}</td>
            <td>${plant.bewaesserung_in_tage}</td>
            <td>${plant.gegossen || 'N/A'}</td>
            <td class="table-actions">
                <button class="btn btn-info btn-sm edit-btn" data-id="${plant.id}">Bearbeiten</button>
                <button class="btn btn-danger btn-sm delete-btn" data-id="${plant.id}">Löschen</button>
            </td>
        `;
    });

    // Add event listeners for new buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            const id = parseInt((event.target as HTMLElement).dataset.id || '0');
            const plantToEdit = plants.find(p => p.id === id);
            if (plantToEdit) {
                populateFormForEdit(plantToEdit);
            }
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            const id = parseInt((event.target as HTMLElement).dataset.id || '0');
            if (confirm(`Sind Sie sicher, dass Sie Pflanze mit ID ${id} löschen möchten?`)) {
                deletePlant(id);
            }
        });
    });
}

// --- API Interaction Functions ---

/**
 * Fetches all plants from the backend API.
 */
async function fetchPlants(): Promise<void> {
    try {
        const response = await fetch(API_BASE_URL);
        const result = await response.json();
        if (result.success) {
            renderPlantsTable(result.data);
        } else {
            showMessage(tableMessage, `Fehler beim Laden der Pflanzen: ${result.message}`, false);
        }
    } catch (error) {
        console.error('Error fetching plants:', error);
        showMessage(tableMessage, 'Verbindungsfehler zum Backend. Server läuft?', false);
    }
}

/**
 * Adds a new plant via the API.
 * @param plantData The data for the new plant.
 */
async function addPlant(plantData: Omit<Pflanze, 'id' | 'created_at' | 'updated_at'>): Promise<void> {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(plantData),
        });
        const result = await response.json();
        if (result.success) {
            showMessage(formMessage, result.message, true);
            resetForm();
            fetchPlants(); // Refresh table
        } else {
            showMessage(formMessage, `Fehler beim Hinzufügen: ${result.message}`, false);
        }
    } catch (error) {
        console.error('Error adding plant:', error);
        showMessage(formMessage, 'Verbindungsfehler zum Backend beim Hinzufügen.', false);
    }
}

/**
 * Updates an existing plant via the API.
 * @param id The ID of the plant to update.
 * @param plantData The data to update.
 */
async function updatePlant(id: number, plantData: Partial<Pflanze>): Promise<void> {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, ...plantData }), // Send ID in body for PUT
        });
        const result = await response.json();
        if (result.success) {
            showMessage(formMessage, result.message, true);
            resetForm();
            fetchPlants(); // Refresh table
        } else {
            showMessage(formMessage, `Fehler beim Aktualisieren: ${result.message}`, false);
        }
    } catch (error) {
        console.error('Error updating plant:', error);
        showMessage(formMessage, 'Verbindungsfehler zum Backend beim Aktualisieren.', false);
    }
}

/**
 * Deletes a plant via the API.
 * @param id The ID of the plant to delete.
 */
async function deletePlant(id: number): Promise<void> {
    try {
        const response = await fetch(API_BASE_URL + `?id=${id}`, { // Using query param for DELETE
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json', // Still send content type even if body is empty
            },
        });
        const result = await response.json();
        if (result.success) {
            showMessage(tableMessage, result.message, true);
            fetchPlants(); // Refresh table
        } else {
            showMessage(tableMessage, `Fehler beim Löschen: ${result.message}`, false);
        }
    } catch (error) {
        console.error('Error deleting plant:', error);
        showMessage(tableMessage, 'Verbindungsfehler zum Backend beim Löschen.', false);
    }
}

// --- Event Listeners ---

plantForm.addEventListener('submit', (event) => {
    event.preventDefault(); // Prevent default form submission

    const id = plantIdInput.value ? parseInt(plantIdInput.value) : 0;
    const name = nameInput.value.trim();
    const kaufdatum = kaufdatumInput.value.trim(); // YYYY-MM-DD
    const standort = standortInput.value.trim();
    const bewaesserung_in_tage = parseInt(bewaesserungInput.value);
    // gegossen input is datetime-local, PHP expects YYYY-MM-DD HH:MM:SS
    const gegossen = gegossenInput.value ? gegossenInput.value.replace('T', ' ') + ':00' : null; // Add seconds if missing

    if (!name || !kaufdatum || !standort || isNaN(bewaesserung_in_tage)) {
        showMessage(formMessage, 'Bitte füllen Sie alle erforderlichen Felder aus.', false);
        return;
    }

    const plantData: Omit<Pflanze, 'id' | 'created_at' | 'updated_at'> = {
        name,
        kaufdatum,
        standort,
        bewaesserung_in_tage,
        gegossen
    };

    if (id) {
        // Update existing plant
        updatePlant(id, plantData);
    } else {
        // Add new plant
        addPlant(plantData);
    }
});

cancelEditBtn.addEventListener('click', resetForm);

// --- Initial Load ---
document.addEventListener('DOMContentLoaded', fetchPlants);
