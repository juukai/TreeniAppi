
// Harjoitusvalikko eri lihasryhmille
const exercises = {
    'Hauis': ['Hauiskääntö', 'Hammerkääntö', 'Scott-penkki', 'Konsentraatiokääntö', 'Vaijerikäännöt'],
    'Ojentaja': ['Pushdown', 'Dippi', 'Ranskalainen puristus', 'Ojentajapunnerrus taljassa', 'Kapea penkkipunnerrus'],
    'Rinta': ['Penkkipunnerrus', 'Vinopenkkipunnerrus', 'Ristikkäistalja', 'Käsipainopunnerrus', 'Flyes'],
    'Olkapäät': ['Pystypunnerrus', 'Vipunostot sivulle', 'Vipunostot eteen', 'Pystysoutu', 'Arnold-punnerrus', 'Takaolkapää taljassa'],
    'Selkä': ['Ylätalja', 'Alatalja', 'Leuanveto', 'Kulmasoutu', 'Maastaveto', 'Selän ojennus', 'T-Bar soutu', 'Pull over taljassa', 'Shrugs', 'Hyvää huomenta -liike'],
    'Jalat': ['Kyykky', 'Prässi', 'Reiden ojennus', 'Reiden koukistus'],
    'Peppu': ['Lantionnosto', 'Bulgarialainen split kyykky', 'RDL', 'Smith kyykky', 'DB reverse lunges', 'Kickback', 'Side kickout'],
    'Pohkeet': ['Pohjepunnerrus seisten', 'Pohjepunnerrus istuen', 'Pohjepunnerrus prässissä'],
    'Vatsat': ['Vatsarutistus', 'Istumaannousut', 'Lankku', 'Jalkojennosto', 'Russian twist']
};

// Alustetaan objekti 'lastSet', joka tallentaa viimeisimmän lisätyn treenisarjan tiedot
let lastSet = { bodyPart: '', exercise: '', weight: '', reps: '' };

// Päivitä harjoitusvalikko valitun lihasryhmän perusteella
function updateExercises(selectElement) {
    const bodyPart = selectElement.value;
    const exerciseSelect = selectElement.parentElement.querySelector('select[name="exercise[]"]');
    
    // Tyhjennetään harjoitusvalikon sisältö ja lisätään uudet vaihtoehdot valitun lihasryhmän perusteella
    exerciseSelect.innerHTML = '<option value="">Valitse harjoitus...</option>';
    if (exercises[bodyPart]) {
        exercises[bodyPart].forEach(function(exercise) {
            // Luodaan uusi vaihtoehto ja lisätään se harjoitusvalikkoon
            const option = document.createElement('option');
            option.value = exercise;
            option.textContent = exercise;
            exerciseSelect.appendChild(option);
        });
    }

    // Päivitä viimeisin sarja valitun lihasryhmän perusteella
    if (selectElement.name === "bodyPart[]") {
        lastSet.bodyPart = selectElement.value;
    }
}

// Lisää uusi sarja lomakkeeseen
function addWorkoutSet() {
    var container = document.getElementById('workout-sets');
    var set = document.createElement('div');
    set.className = 'workout-set';
    set.innerHTML = `
        <label for="bodyPart">Lihasryhmä:</label>
        <select name="bodyPart[]" onchange="updateExercises(this)" required>
            <option value="">Valitse lihasryhmä...</option>
            <option value="Hauis">Hauis</option>
            <option value="Ojentaja">Ojentaja</option>
            <option value="Rinta">Rinta</option>
            <option value="Olkapäät">Olkapäät</option>
            <option value="Selkä">Selkä</option>
            <option value="Jalat">Jalat</option>
            <option value="Peppu">Peppu</option>
            <option value="Pohkeet">Pohkeet</option>
            <option value="Vatsat">Vatsat</option>
        </select>

        <label for="exercise">Harjoitus:</label>
        <select name="exercise[]" required>
            <option value="">Valitse harjoitus...</option>
        </select>

        <label for="weight">Paino (kg):</label>
        <input type="number" name="weight[]" min="0" required>

        <label for="reps">Toistot:</label>
        <input type="number" name="reps[]" min="0" required>

        <button type="button" onclick="removeWorkoutSet(this)">Poista sarja</button>
    `;
    container.appendChild(set);

    // Aseta arvot viimeisimmän sarjan perusteella
    set.querySelector('select[name="bodyPart[]"]').value = lastSet.bodyPart;
    updateExercises(set.querySelector('select[name="bodyPart[]"]'));
    set.querySelector('select[name="exercise[]"]').value = lastSet.exercise;
}

// Poista sarja
function removeWorkoutSet(button) {
    var set = button.parentElement;
    set.remove();
}

// Päivitä viimeisimmän sarjan tiedot, kun niitä muutetaan
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === "exercise[]") {
        lastSet.exercise = e.target.value;
    }
});