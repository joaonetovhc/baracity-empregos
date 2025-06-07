document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('token');
  const nome = localStorage.getItem('nome');
  const nav = document.getElementById('user-nav');
  const container = document.getElementById('candidatos-container');

  if (!token) {
    alert('Você precisa estar logado.');
    window.location.href = 'login.html';
    return;
  }

  // Topbar login info
  if (nome && nome.trim()) {
    nav.innerHTML = `
      <span>Olá, ${nome}</span>
      <a href="#" onclick="logout()" style="margin-left: 20px;">Sair</a>
    `;
  }

  fetch(`http://localhost/baracity-empregos/api/listar_candidatos.php?token=${token}`)
    .then(res => res.json())
    .then(candidatos => {
      if (candidatos.erro) {
        container.innerHTML = `<p>${candidatos.erro}</p>`;
        return;
      }

      if (candidatos.length === 0) {
        container.innerHTML = '<p>Nenhum candidato encontrado para suas vagas.</p>';
        return;
      }

      candidatos.forEach(c => {
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
          <h2>${c.candidato}</h2>
          <p><strong>Email:</strong> ${c.email}</p>
          <p><strong>Vaga:</strong> ${c.vaga}</p>
          <p><strong>Data da candidatura:</strong> ${new Date(c.data_candidatura).toLocaleDateString()}</p>
        `;
        container.appendChild(div);
      });
    })
    .catch(err => {
      console.error('Erro ao carregar candidatos:', err);
      container.innerHTML = '<p>Erro ao carregar os candidatos.</p>';
    });
});

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}
