document.addEventListener('DOMContentLoaded', function () {
  const token = localStorage.getItem('token');
  const nome = localStorage.getItem('nome');
  const nav = document.getElementById('user-nav');


  // Verifica se está logado
  if (!token) {
    alert('Você precisa estar logado.');
    window.location.href = 'login.html';
    return;
  }

if (nome && nome.trim()) {
    nav.innerHTML = `
      <span>Olá, ${nome}</span>
      <a href="#" onclick="logout()" style="margin-left: 20px;">Sair</a>
    `;
  } else {
    nav.innerHTML = `
      <a href="login.html">Entrar</a>
      <a href="cadastrar.html">Cadastrar</a>
    `;
  }

  fetch(`http://localhost/A3/baracity-empregos/api/listar_vagas.php?token=${token}`)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('vagas-container');

      if (!data.vagas || data.vagas.length === 0) {
        container.innerHTML = '<p>Nenhuma vaga disponível no momento.</p>';
        return;
      }

      data.vagas.forEach(vaga => {
        const card = document.createElement('div');
        card.classList.add('vaga-card');

        card.innerHTML = `
          <div class="vaga-content">
            <h3>${vaga.titulo}</h3>
            <p><strong>Empresa:</strong> ${vaga.nome_empresa}</p>
            <p class="descricao"><strong>Descrição:</strong> ${vaga.descricao}</p>
            <p><strong>Requisitos:</strong> ${vaga.requisitos || 'Não informado'}</p>
            <p><strong>Salário:</strong> R$ ${parseFloat(vaga.salario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
            <p><strong>Data da publicação:</strong> ${new Date (vaga.data_publicacao).toLocaleDateString('pt-br')}</p>
          </div>
          <button onclick="candidatar(${vaga.id})">Candidatar-se</button>
        `;

        container.appendChild(card);
      });
    })
    .catch(error => {
      console.error('Erro ao carregar vagas:', error);
      document.getElementById('vagas-container').innerHTML = '<p>Erro ao carregar as vagas.</p>';
    });
});

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}

function candidatar(id_vaga) {
  const token = localStorage.getItem('token');
  
  const curriculo = 'asdaas';

  fetch(`http://localhost/A3/baracity-empregos/api/candidatar.php?token=${token}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id_vaga: id_vaga,
      curriculo: curriculo
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.sucesso) {
      alert(data.sucesso);
    } else if (data.erro) {
      alert('Erro: ' + data.erro);
    }
  })
  .catch(error => {
    console.error('Erro ao enviar candidatura:', error);
    alert('Erro ao se candidatar. Tente novamente mais tarde.');
  });
}