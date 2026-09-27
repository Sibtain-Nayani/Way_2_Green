<!-- Map Picker Modal -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.map-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.map-modal {
    background: #fff;
    width: 90%; max-width: 600px;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: flex; flex-direction: column; gap: 15px;
}
.map-modal-header {
    display: flex; justify-content: space-between; align-items: center;
}
.map-modal-header h3 { margin: 0; color: #073B2A; font-size: 1.3rem; }
.map-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
.map-search-box {
    display: flex; gap: 10px;
}
.map-search-input {
    flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;
}
.map-search-btn {
    padding: 10px 16px; background: #29AB87; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;
}
#picker-map { height: 350px; width: 100%; border-radius: 8px; border: 1px solid #eee; }
.map-confirm-btn {
    padding: 12px; background: #073B2A; color: white; border: none; border-radius: 6px;
    font-size: 1rem; font-weight: bold; cursor: pointer; text-align: center;
    transition: background 0.2s;
}
.map-confirm-btn:hover { background: #0a4f38; }
.map-confirm-btn:disabled { background: #ccc; cursor: not-allowed; }
.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10000;
    display: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.autocomplete-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
}
.autocomplete-item:hover {
    background: #f0fdf4;
}
.autocomplete-item:last-child {
    border-bottom: none;
}
</style>

<div class="map-modal-overlay" id="mapModalOverlay">
    <div class="map-modal">
        <div class="map-modal-header">
            <h3>Select Location</h3>
            <button class="map-close-btn" onclick="closeMapPicker()">&times;</button>
        </div>
        <div class="map-search-box" style="position: relative;">
            <input type="text" id="mapSearchInput" class="map-search-input" placeholder="Search for a city or place..." oninput="debounceSearch(this.value)" onkeypress="if(event.key === 'Enter') searchMapLocation()">
            <button class="map-search-btn" onclick="searchMapLocation()">Search</button>
            <div id="autocompleteResults" class="autocomplete-results"></div>
        </div>
        <div id="picker-map"></div>
        <button class="map-confirm-btn" id="mapConfirmBtn" disabled onclick="confirmMapLocation()">Confirm Selected Location</button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let pickerMap = null;
let pickerMarker = null;
let currentTargetInputId = null;
let selectedLocationName = "";

function openMapPicker(inputId) {
    currentTargetInputId = inputId;
    document.getElementById('mapModalOverlay').style.display = 'flex';
    
    // Initialize map if not already done
    if (!pickerMap) {
        pickerMap = L.map('picker-map').setView([20.5937, 78.9629], 5); // Default to India
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors', maxZoom: 18
        }).addTo(pickerMap);

        pickerMap.on('click', function(e) {
            setMapMarker(e.latlng.lat, e.latlng.lng, "Selected Pin");
            // Reverse geocode to get name
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(res => res.json())
                .then(data => {
                    if(data.display_name) {
                        let shortName = data.address.city || data.address.town || data.address.village || data.name || data.display_name.split(',')[0];
                        selectedLocationName = shortName;
                        pickerMarker.bindPopup(`<b>${shortName}</b>`).openPopup();
                        document.getElementById('mapConfirmBtn').disabled = false;
                        document.getElementById('mapConfirmBtn').innerText = `Confirm: ${shortName}`;
                    }
                });
        });
    }
    setTimeout(() => { pickerMap.invalidateSize(); }, 200);
}

function closeMapPicker() {
    document.getElementById('mapModalOverlay').style.display = 'none';
}

function setMapMarker(lat, lon, name) {
    if (pickerMarker) pickerMap.removeLayer(pickerMarker);
    pickerMarker = L.marker([lat, lon]).addTo(pickerMap);
    pickerMap.setView([lat, lon], 12);
    selectedLocationName = name;
    document.getElementById('mapConfirmBtn').disabled = false;
    document.getElementById('mapConfirmBtn').innerText = `Confirm: ${name}`;
}

let searchTimeout = null;

function debounceSearch(query) {
    clearTimeout(searchTimeout);
    if (!query || query.length < 3) {
        document.getElementById('autocompleteResults').style.display = 'none';
        return;
    }
    searchTimeout = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
            .then(res => res.json())
            .then(data => {
                const resultsContainer = document.getElementById('autocompleteResults');
                resultsContainer.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(loc => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerText = loc.display_name;
                        div.onclick = () => {
                            let shortName = loc.name || loc.display_name.split(',')[0];
                            setMapMarker(loc.lat, loc.lon, shortName);
                            pickerMarker.bindPopup(`<b>${shortName}</b>`).openPopup();
                            document.getElementById('mapSearchInput').value = loc.display_name;
                            resultsContainer.style.display = 'none';
                        };
                        resultsContainer.appendChild(div);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            });
    }, 400); // 400ms debounce
}

function searchMapLocation() {
    const q = document.getElementById('mapSearchInput').value;
    if (!q) return;
    document.getElementById('autocompleteResults').style.display = 'none';
    
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const loc = data[0];
                let shortName = loc.name || loc.display_name.split(',')[0];
                setMapMarker(loc.lat, loc.lon, shortName);
                pickerMarker.bindPopup(`<b>${shortName}</b>`).openPopup();
            } else {
                alert("Location not found. Try a different search.");
            }
        });
}

// Close autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.map-search-box')) {
        document.getElementById('autocompleteResults').style.display = 'none';
    }
});

function confirmMapLocation() {
    if (currentTargetInputId && selectedLocationName) {
        document.getElementById(currentTargetInputId).value = selectedLocationName;
    }
    closeMapPicker();
}
</script>
