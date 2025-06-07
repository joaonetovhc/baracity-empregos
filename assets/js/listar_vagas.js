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
      <a href="#" onclick="candidaturas()" style="margin-left: 20px;">Candidaturas</a>
      <a href="#" onclick="logout()" style="margin-left: 20px;">Sair</a>
    `;
  } else {
    nav.innerHTML = `
      <a href="login.html">Entrar</a>
      <a href="cadastrar.html">Cadastrar</a>
    `;
  }

  fetch(`http://localhost/baracity-empregos/api/listar_vagas.php?token=${token}`)
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
            <p><strong>Data da publicação:</strong> ${new Date(vaga.data_publicacao).toLocaleDateString('pt-br')}</p>
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

function candidaturas(){
  window.location.href = 'candidaturas.html';
}

function candidatar(idVaga) {
  const token = localStorage.getItem('token');

  fetch(`http://localhost/baracity-empregos/api/verifica_candidatura.php?token=${token}&id_vaga=${idVaga}`)
    .then(response => response.json())
    .then(data => {
      if (data.ja_candidatou) {
        alert('Você já se candidatou a essa vaga.');
      } else {
        document.getElementById('vaga-id').value = idVaga;
        document.getElementById('modal-candidatura').style.display = 'flex';
      }
    })
    .catch(error => {
      console.error('Erro ao verificar candidatura:', error);
      alert('Erro ao verificar candidatura. Tente novamente mais tarde.');
    });
}

function fecharModal() {
  document.getElementById('modal-candidatura').style.display = 'none';
  document.getElementById('vaga-id').value = '';
  document.getElementById('curriculo').value = '';
}

document.getElementById('form-candidatura').addEventListener('submit', function(event) {
  event.preventDefault();

  const token = localStorage.getItem('token');
  const vagaId = document.getElementById('vaga-id').value;
  const arquivoInput = document.getElementById('curriculo');

  if (arquivoInput.files.length === 0) {
    alert('Por favor, selecione um arquivo de currículo.');
    return;
  }

  const arquivo = arquivoInput.files[0];
  const formData = new FormData();

  formData.append('id_vaga', vagaId);
  formData.append('curriculo', arquivo);

  fetch(`http://localhost/baracity-empregos/api/candidatar.php?token=${token}`, {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) throw new Error('HTTP status ' + response.status);
    return response.json();
  })
  .then(data => {
    if (data.sucesso) {
      alert(data.sucesso);
      fecharModal();
    } else {
      alert('Erro: ' + (data.erro || 'Erro desconhecido'));
    }
  })
  .catch(error => {
    console.error('Erro ao enviar candidatura:', error);
    alert('Erro ao se candidatar. Tente novamente mais tarde.');
  });
});
