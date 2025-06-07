document.addEventListener('DOMContentLoaded', function () {
  const token = localStorage.getItem('token');
  const nav = document.getElementById('user-nav');

  // Verifica se está logado
  if (!token) {
    alert('Você precisa estar logado.');
    window.location.href = 'login.html';
    return;
  }

  // Exibe o nome no menu, se existir
  const nome = localStorage.getItem('nome');
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

  fetch(`http://localhost/A3/baracity-empregos/api/vagas_empresa.php?token=${token}`)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('vagas-container');

      if (!data.vagas || data.vagas.length === 0) {
        container.innerHTML = '<p>Nenhuma vaga cadastrada.</p>';
        return;
      }

      data.vagas.forEach(vaga => {
        const card = document.createElement('div');
        card.classList.add('vaga-card');

        card.innerHTML = `
          <div class="vaga-content">
            <h3>${vaga.titulo}</h3>
            <p><strong>Descrição:</strong> ${vaga.descricao}</p>
            <p><strong>Requisitos:</strong> ${vaga.requisitos || 'Não informado'}</p>
            <p><strong>Salário:</strong> R$ ${parseFloat(vaga.salario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
            <p><strong>Data da publicação:</strong> ${new Date (vaga.data_publicacao).toLocaleDateString('pt-br')}</p>
          </div>
          <button class="btn-editar" onclick="editarVaga(${vaga.id})">Editar</button>
          <button class="btn-excluir" onclick="excluirVaga(${vaga.id})">Excluir</button>
        `;

        container.appendChild(card);
      });
    })
    .catch(error => {
      console.error('Erro ao carregar vagas da empresa:', error);
      document.getElementById('vagas-container').innerHTML = '<p>Erro ao carregar as vagas.</p>';
    });
});

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}

function editarVaga(id) {
  window.location.href = `editar_vaga.html?id=${id}`;
}

function excluirVaga(id) {
  if (confirm('Tem certeza que deseja excluir esta vaga?')) {
    const token = localStorage.getItem('token');
    fetch(`http://localhost/A3/baracity-empregos/api/excluir_vaga.php?id=${id}&token=${token}`, {
      method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
      if (data.sucesso) {
        alert('Vaga excluída com sucesso.');
        window.location.reload();
      } else {
        alert(data.erro || 'Erro ao excluir vaga.');
      }
    })
    .catch(error => {
      console.error('Erro ao excluir vaga:', error);
      alert('Erro ao excluir vaga.');
    });
  }
}
