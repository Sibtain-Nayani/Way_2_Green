/**
 * Way2Green — Trip Search Component
 * Mock data & UI logic for the homepage journey planner.
 *
 * FUTURE API INTEGRATION — PHASE 7
 * Replace MOCK_LOCATIONS and MOCK_JOURNEYS with real API calls.
 */

// ---------------------------------------------------------------------------
// MOCK DATA — Replace with API calls in Phase 7
// ---------------------------------------------------------------------------

/** @type {Array<{id:string, label:string, country:string}>} */
const MOCK_LOCATIONS = [
    { id: 'del', label: 'New Delhi', country: 'India' },
    { id: 'mum', label: 'Mumbai',   country: 'India' },
    { id: 'ban', label: 'Bengaluru', country: 'India' },
    { id: 'che', label: 'Chennai',  country: 'India' },
    { id: 'kol', label: 'Kolkata',  country: 'India' },
    { id: 'hyd', label: 'Hyderabad', country: 'India' },
    { id: 'coc', label: 'Kochi',    country: 'India' },
    { id: 'mun', label: 'Munnar',   country: 'India' },
    { id: 'man', label: 'Manali',   country: 'India' },
    { id: 'way', label: 'Wayanad',  country: 'India' },
    { id: 'rsh', label: 'Rishikesh', country: 'India' },
    { id: 'goa', label: 'Goa',      country: 'India' },
    { id: 'oot', label: 'Ooty',     country: 'India' },
];

/**
 * Mock journey results keyed by "from-to".
 * FUTURE API INTEGRATION — PHASE 7
 */
const MOCK_JOURNEYS = {
    'del-mun': [
        {
            mode: 'Train',
            icon: '🚆',
            label: 'Rajdhani Express',
            duration: '32h',
            price: 1850,
            co2: 14,
            tag: 'Way2Green Pick',
            tagClass: 'tag-w2g',
        },
        {
            mode: 'Flight',
            icon: '✈️',
            label: 'IndiGo / Air India',
            duration: '2h 20m',
            price: 4200,
            co2: 185,
            tag: 'Fastest',
            tagClass: 'tag-fastest',
        },
        {
            mode: 'Bus',
            icon: '🚌',
            label: 'Volvo A/C Sleeper',
            duration: '14h',
            price: 950,
            co2: 22,
            tag: 'Lowest Price',
            tagClass: 'tag-price',
        },
    ],
    'del-ban': [
        {
            mode: 'Train',
            icon: '🚆',
            label: 'Karnataka Express',
            duration: '40h',
            price: 2100,
            co2: 16,
            tag: 'Way2Green Pick',
            tagClass: 'tag-w2g',
        },
        {
            mode: 'Flight',
            icon: '✈️',
            label: 'IndiGo Direct',
            duration: '2h 40m',
            price: 5500,
            co2: 210,
            tag: 'Fastest',
            tagClass: 'tag-fastest',
        },
    ],
    'default': [
        {
            mode: 'Train',
            icon: '🚆',
            label: 'Express Service',
            duration: '8h–36h',
            price: 1200,
            co2: 10,
            tag: 'Way2Green Pick',
            tagClass: 'tag-w2g',
        },
        {
            mode: 'Flight',
            icon: '✈️',
            label: 'Direct Flight',
            duration: '1h–3h',
            price: 4800,
            co2: 190,
            tag: 'Fastest',
            tagClass: 'tag-fastest',
        },
        {
            mode: 'Bus',
            icon: '🚌',
            label: 'A/C Sleeper Coach',
            duration: '8h–16h',
            price: 800,
            co2: 18,
            tag: 'Lowest Price',
            tagClass: 'tag-price',
        },
    ],
};

// ---------------------------------------------------------------------------
// DOM HELPERS
// ---------------------------------------------------------------------------

function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

function showError(field, msg) {
    var err = document.getElementById(field + '-err');
    if (err) { err.textContent = msg; err.hidden = false; }
}

function clearError(field) {
    var err = document.getElementById(field + '-err');
    if (err) { err.textContent = ''; err.hidden = true; }
}

function clearAllErrors() {
    ['from', 'to', 'departure', 'return'].forEach(clearError);
}

// ---------------------------------------------------------------------------
// AUTOCOMPLETE — populate <datalist> from MOCK_LOCATIONS
// FUTURE API INTEGRATION — PHASE 7: replace with debounced API call
// ---------------------------------------------------------------------------
function initLocationDatalist() {
    var lists = document.querySelectorAll('.location-datalist');
    lists.forEach(function(dl) {
        MOCK_LOCATIONS.forEach(function(loc) {
            var opt = document.createElement('option');
            opt.value = loc.label;
            dl.appendChild(opt);
        });
    });
}

// ---------------------------------------------------------------------------
// TRIP TYPE TOGGLE (One Way / Round Trip)
// ---------------------------------------------------------------------------
function initTripTypeToggle() {
    var toggleBtns = document.querySelectorAll('.trip-type-btn');
    var returnField = document.getElementById('return-field-wrap');
    if (!returnField) return;

    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var type = btn.dataset.type;
            returnField.style.display = type === 'round' ? '' : 'none';
            if (type === 'one-way') clearError('return');
        });
    });
}

// ---------------------------------------------------------------------------
// PREFERENCE PILLS
// ---------------------------------------------------------------------------
function initPreferencePills() {
    var pills = document.querySelectorAll('.pref-pill');
    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            pills.forEach(function(p) { p.classList.remove('active'); });
            pill.classList.add('active');
        });
    });
}

// ---------------------------------------------------------------------------
// VALIDATION
// ---------------------------------------------------------------------------
function validateForm(data) {
    var valid = true;
    clearAllErrors();

    if (!data.from.trim()) {
        showError('from', 'Please enter a departure city.');
        valid = false;
    }
    if (!data.to.trim()) {
        showError('to', 'Please enter a destination.');
        valid = false;
    }
    if (!data.departure) {
        showError('departure', 'Please select a departure date.');
        valid = false;
    } else {
        var dep = new Date(data.departure);
        var today = new Date(); today.setHours(0,0,0,0);
        if (dep < today) {
            showError('departure', 'Departure date cannot be in the past.');
            valid = false;
        }
    }
    if (data.tripType === 'round' && data.returnDate) {
        var dep2 = new Date(data.departure);
        var ret  = new Date(data.returnDate);
        if (ret < dep2) {
            showError('return', 'Return date cannot be before departure.');
            valid = false;
        }
    }
    return valid;
}

// ---------------------------------------------------------------------------
// JOURNEY RESULTS RENDERER
// FUTURE API INTEGRATION — PHASE 7: swap lookupJourneys with API fetch
// ---------------------------------------------------------------------------
function lookupJourneys(fromLabel, toLabel) {
    var fromLoc = MOCK_LOCATIONS.find(function(l) {
        return l.label.toLowerCase() === fromLabel.toLowerCase();
    });
    var toLoc = MOCK_LOCATIONS.find(function(l) {
        return l.label.toLowerCase() === toLabel.toLowerCase();
    });

    if (fromLoc && toLoc) {
        var key = fromLoc.id + '-' + toLoc.id;
        return MOCK_JOURNEYS[key] || MOCK_JOURNEYS['default'];
    }
    return MOCK_JOURNEYS['default'];
}

function renderResults(journeys, container) {
    container.innerHTML = '';
    journeys.forEach(function(j, idx) {
        var card = document.createElement('div');
        card.className = 'journey-result-card';
        card.style.animationDelay = (idx * 0.07) + 's';
        card.innerHTML =
            '<div class="jrc-mode">' +
                '<span class="jrc-icon">' + j.icon + '</span>' +
                '<div>' +
                    '<div class="jrc-mode-name">' + j.mode + '</div>' +
                    '<div class="jrc-label">' + j.label + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="jrc-meta">' +
                '<span class="jrc-duration">\u23F1 ' + j.duration + '</span>' +
                '<span class="jrc-co2 ' + (j.co2 < 50 ? 'co2-low' : 'co2-high') + '">\uD83C\uDF31 ' + j.co2 + ' kg CO\u2082</span>' +
            '</div>' +
            '<div class="jrc-right">' +
                '<span class="jrc-price">\u20B9' + j.price.toLocaleString('en-IN') + '</span>' +
                '<span class="jrc-tag ' + j.tagClass + '">' + j.tag + '</span>' +
            '</div>';
        container.appendChild(card);
    });
}

// ---------------------------------------------------------------------------
// FORM SUBMIT
// ---------------------------------------------------------------------------
function initSearchForm() {
    var form = document.getElementById('trip-search-form');
    if (!form) return;

    var resultsWrap = document.getElementById('journey-results-wrap');
    var resultsList = document.getElementById('journey-results-list');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var activeTypeBtn = document.querySelector('.trip-type-btn.active');
        var tripType   = activeTypeBtn ? activeTypeBtn.dataset.type : 'round';
        var fromVal    = document.getElementById('trip-from') ? document.getElementById('trip-from').value : '';
        var toVal      = document.getElementById('trip-to') ? document.getElementById('trip-to').value : '';
        var departure  = document.getElementById('trip-departure') ? document.getElementById('trip-departure').value : '';
        var returnDate = document.getElementById('trip-return') ? document.getElementById('trip-return').value : '';
        var travellers = document.getElementById('trip-travellers') ? document.getElementById('trip-travellers').value : '1';
        var activePref = document.querySelector('.pref-pill.active');
        var preference = activePref ? activePref.dataset.pref : 'w2g';

        var data = { tripType: tripType, from: fromVal, to: toVal, departure: departure, returnDate: returnDate, travellers: travellers, preference: preference };

        if (!validateForm(data)) return;

        // FUTURE API INTEGRATION — PHASE 7: replace lookupJourneys with API call
        var journeys = lookupJourneys(fromVal, toVal);
        renderResults(journeys, resultsList);
        if (resultsWrap) {
            resultsWrap.hidden = false;
            resultsWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}

// ---------------------------------------------------------------------------
// INIT
// ---------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    initLocationDatalist();
    initTripTypeToggle();
    initPreferencePills();
    initSearchForm();
});
