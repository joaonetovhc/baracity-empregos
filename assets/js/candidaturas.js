document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('token');
  const nome = localStorage.getItem('nome');
  const nav = document.getElementById('user-nav');
  const container = document.getElementById('candidaturas-container');

  if (!token) {
    alert('Você precisa estar logado.');
    window.location.href = 'login.html';
    return;
  }

  if (nome && nome.trim()) {
    nav.innerHTML = `
      <span>Olá, ${nome}</span>
      <a href="#" onclick="vagas()" style="margin-left: 20px;">Vagas</a>
      <a href="#" onclick="logout()" style="margin-left: 20px;">Sair</a>
    `;
  } else {
    nav.innerHTML = `
      <a href="login.html">Entrar</a>
      <a href="cadastrar.html">Cadastrar</a>
    `;
  }

  fetch(`http://localhost/baracity-empregos/api/listar_candidaturas.php?token=${token}`)
    .then(res => res.json())
    .then(candidaturas => {
      if (candidaturas.erro) {
        container.innerHTML = `<p>${candidaturas.erro}</p>`;
        return;
      }

      if (candidaturas.length === 0) {
        container.innerHTML = '<p>Você ainda não se candidatou a nenhuma vaga.</p>';
        return;
      }

      candidaturas.forEach(c => {
        const div = document.createElement('div');
        div.className = 'vaga-card';
        div.innerHTML = `
          <h3>${c.titulo}</h3>
          <p><strong>Empresa:</strong> ${c.empresa}</p>
          <p><strong>Local:</strong> ${c.local}</p>
          <p><strong>Salário:</strong> R$ ${c.salario}</p>
          <p><strong>Data da candidatura:</strong> ${new Date(c.data_candidatura).toLocaleDateString()}</p>
          <button class="btn-remover" data-id="${c.id}" style="margin-top: 8px; padding: 6px 12px; cursor: pointer;">Remover candidatura</button>
        `;
        container.appendChild(div);
      });
    })
    .catch(err => {
      console.error('Erro ao carregar candidaturas:', err);
      container.innerHTML = '<p>Erro ao carregar suas candidaturas.</p>';
  });

  container.addEventListener('click', e => {
    if (e.target.classList.contains('btn-remover')) {
      const idCandidatura = e.target.getAttribute('data-id');
      if (confirm('Tem certeza que quer remover essa candidatura?')) {
        removerCandidatura(idCandidatura);
      }
    }
  });

  function removerCandidatura(id) {
    fetch(`http://localhost/baracity-empregos/api/remover_candidatura.php?token=${token}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({id:id})
    })
    .then(res => res.json())
    .then(data => {
      if (data.sucesso) {
        alert('Candidatura removida com sucesso.');
        location.reload();
      } else {
        alert('Erro: ' + (data.erro || 'Não foi possível remover.'));
      }
    })
    .catch(err => {
      console.error('Erro ao remover candidatura:', err);
      alert('Erro ao remover candidatura.');
    });
  }
});

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}

function vagas() {
  window.location.href = 'listar_vagas.html';
}