document.addEventListener('DOMContentLoaded', function() {
    document.body.style.opacity = '1';
    initNavbar();
    initStatsCounter();
    initContactForm();
    initPWDForm();
    initFamilyTable();
    initPageTransitions();
    hidePageLoader();
});

function initPageTransitions() {
    const links = document.querySelectorAll('a[href$=".html"], a[href$=".php"]');
    
    links.forEach(link => {
        if (link.href && !link.href.includes('#') && !link.target) {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('http') && !href.startsWith('mailto:')) {
                    e.preventDefault();
                    showPageLoader(() => {
                        window.location.href = href;
                    });
                }
            });
        }
    });
}

function showPageLoader(callback) {
    let loader = document.querySelector('.page-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'page-loader';
        loader.innerHTML = '<div class="page-loader-spinner"></div>';
        document.body.appendChild(loader);
    }
    loader.classList.remove('hidden');
    
    setTimeout(() => {
        if (callback) callback();
    }, 500);
}

function hidePageLoader() {
    const loader = document.querySelector('.page-loader');
    if (loader) {
        loader.classList.add('hidden');
    }
}

window.showPageLoader = showPageLoader;

function initNavbar() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
}

function initStatsCounter() {
    const statNumbers = document.querySelectorAll('.stat-number[data-count]');
    
    if (statNumbers.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const count = parseInt(el.dataset.count);
                animateCounter(el, count);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    
    statNumbers.forEach(stat => observer.observe(stat));
}

function animateCounter(element, target) {
    let current = 0;
    const increment = target / 50;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 30);
}

function initContactForm() {}

function handleContactSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(document.getElementById('contactForm'));
    
    fetch('backend/submit.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('contactForm').reset();
            document.getElementById('contactSuccess').style.display = 'block';
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('Error sending message. Please try again.');
    });
}

function initPWDForm() {
    const pwdForm = document.getElementById('pwdForm');
    if (!pwdForm) return;
    
    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');
    
    if (birthdateInput && ageInput) {
        birthdateInput.addEventListener('change', function() {
            const birthdate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const monthDiff = today.getMonth() - birthdate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }
            
            if (age > 0 && age < 150) {
                ageInput.value = age;
            }
        });
    }
    
    pwdForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        const familyMembers = getFamilyMembersData();
        console.log('Family Members Data:', familyMembers);
        formData.append('family_members', JSON.stringify(familyMembers));
        
        fetch('backend/submit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            console.log('Response:', data);
            if (data.success) {
                showModal();
                pwdForm.reset();
                clearFamilyTable();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again. ' + error.message);
        });
    });
}

function initFamilyTable() {
    const addBtn = document.getElementById('addFamilyMember');
    if (!addBtn) return;
    
    addBtn.addEventListener('click', function() {
        addFamilyRow();
    });
}

function addFamilyRow() {
    const tbody = document.querySelector('#familyTable tbody');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="familyName[]" placeholder="Name"></td>
        <td><input type="number" name="familyAge[]" placeholder="Age" min="1" max="150"></td>
        <td>
            <select name="familyCivilStatus[]">
                <option value="">Select</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
            </select>
        </td>
        <td><input type="text" name="familyRelationship[]" placeholder="Relationship"></td>
        <td><input type="text" name="familyOccupation[]" placeholder="Occupation"></td>
        <td><button type="button" class="delete-btn" onclick="deleteFamilyRow(this)"><i class="fa-solid fa-trash"></i></button></td>
    `;
    
    tbody.appendChild(row);
}

function deleteFamilyRow(btn) {
    const row = btn.closest('tr');
    const tbody = row.closest('tbody');
    row.remove();
}

function getFamilyMembersData() {
    const members = [];
    const rows = document.querySelectorAll('#familyTable tbody tr');
    
    rows.forEach(row => {
        const inputs = row.querySelectorAll('input, select');
        if (inputs[0].value.trim()) {
            members.push({
                name: inputs[0].value,
                age: inputs[1].value,
                civil_status: inputs[2].value,
                relationship: inputs[3].value,
                occupation: inputs[4].value
            });
        }
    });
    
    return members;
}

function clearFamilyTable() {
    const tbody = document.querySelector('#familyTable tbody');
    if (tbody) {
        tbody.innerHTML = '';
    }
}

function showModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

window.closeModal = closeModal;
window.deleteFamilyRow = deleteFamilyRow;