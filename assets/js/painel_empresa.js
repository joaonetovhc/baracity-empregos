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

  fetch(`http://localhost/baracity-empregos/api/vagas_empresa.php?token=${token}`)
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

function excluirVaga(id) {
  if (confirm('Tem certeza que deseja excluir esta vaga?')) {
    const token = localStorage.getItem('token');
    fetch(`http://localhost/baracity-empregos/api/inativar_vaga.php?id=${id}&token=${token}`, {
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


const modal = document.getElementById('modal-editar');
const fecharModal = document.getElementById('fechar-modal');
const formEditar = document.getElementById('form-editar-vaga');

function editarVaga(id) {
  // Pega a vaga no DOM pelo id
  const card = [...document.querySelectorAll('.vaga-card')].find(c => 
    c.querySelector('.btn-editar').getAttribute('onclick').includes(`editarVaga(${id})`)
  );

  if (!card) return alert('Erro ao encontrar a vaga para editar.');

  // Preenche o modal com os dados da vaga do card
  document.getElementById('vaga-id').value = id;
  document.getElementById('vaga-titulo').value = card.querySelector('h3').textContent;
  document.getElementById('vaga-descricao').value = card.querySelector('p:nth-of-type(1)').textContent.replace('Descrição: ', '');
  document.getElementById('vaga-requisitos').value = card.querySelector('p:nth-of-type(2)').textContent.replace('Requisitos: ', '');
  const salarioTexto = card.querySelector('p:nth-of-type(3)').textContent.replace('Salário: R$ ', '').replace('.', '').replace(',', '.');
  document.getElementById('vaga-salario').value = parseFloat(salarioTexto);

  modal.style.display = 'flex';
}

fecharModal.onclick = () => {
  modal.style.display = 'none';
};

window.onclick = (event) => {
  if (event.target == modal) {
    modal.style.display = 'none';
  }
};

formEditar.addEventListener('submit', function(e) {
  e.preventDefault();

  const token = localStorage.getItem('token');
  const formData = new FormData(formEditar);
  const data = Object.fromEntries(formData.entries());

fetch(`http://localhost/baracity-empregos/api/editar_vaga.php?token=${token}`, {
    method: 'PUT', // ou POST dependendo da sua API
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(resData => {
    if(resData.sucesso) {
      alert('Vaga atualizada com sucesso!');
      modal.style.display = 'none';
      // Atualiza o card com os novos dados sem recarregar
      atualizarCardVaga(data);
    } else {
      alert(resData.erro || 'Erro ao atualizar vaga.');
    }
  })
  .catch(err => {
    alert('Erro na comunicação com a API.');
    console.error(err);
  });
});

function atualizarCardVaga(vagaAtualizada) {
  const cards = document.querySelectorAll('.vaga-card');
  cards.forEach(card => {
    if (card.querySelector('.btn-editar').getAttribute('onclick').includes(`editarVaga(${vagaAtualizada.id})`)) {
      card.querySelector('h3').textContent = vagaAtualizada.titulo;
      card.querySelector('p:nth-of-type(1)').innerHTML = `<strong>Descrição:</strong> ${vagaAtualizada.descricao}`;
      card.querySelector('p:nth-of-type(2)').innerHTML = `<strong>Requisitos:</strong> ${vagaAtualizada.requisitos || 'Não informado'}`;
      card.querySelector('p:nth-of-type(3)').innerHTML = `<strong>Salário:</strong> R$ ${parseFloat(vagaAtualizada.salario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    }
  });
}