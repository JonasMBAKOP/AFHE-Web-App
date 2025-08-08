// Script de la page "users/list.php"

// Script pour la recherche dans le tableau des utilisateurs
document.addEventListener('DOMContentLoaded', () => {
  const input     = document.getElementById('searchInput');
  const table     = document.querySelector('.users-table');
  const rows      = Array.from(table.querySelectorAll('tr'));
  const headerRow = rows.shift(); // on retire la ligne d'en-tête
  let noResultRow = null;

  input.addEventListener('input', () => {
    const term = input.value.trim().toLowerCase();
    let visibleCount = 0;

    // Parcours des lignes de données
    rows.forEach(row => {
      const cells       = row.querySelectorAll('td');
      const fullName    = cells[0].textContent.toLowerCase();
      const userName    = cells[1].textContent.toLowerCase();
      const match       = fullName.includes(term) || userName.includes(term);

      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    // Gère l'affichage d'une ligne "Aucun résultat"
    if (visibleCount === 0) {
      if (!noResultRow) {
        noResultRow = document.createElement('tr');
        noResultRow.classList.add('no-result');
        noResultRow.innerHTML = `
          <td colspan="${headerRow.children.length}" class="no-data">
            Aucun administrateur ne correspond à la recherche.
          </td>`;
        table.appendChild(noResultRow);
      }
    } else if (noResultRow) {
      table.removeChild(noResultRow);
      noResultRow = null;
    }
  });
});


// Script de la page "users/add.php"

// Script pour le toggle de visibilité du mot de passe
document.addEventListener("DOMContentLoaded", function () {
    const togglePassword = document.getElementById("togglePassword");
    const passwordField  = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
        if (passwordField.type === "password") {
            passwordField.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
});

