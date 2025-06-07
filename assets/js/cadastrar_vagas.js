document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-vaga');
  const token = localStorage.getItem('token');
  const nome = localStorage.getItem('nome');
  const nav = document.getElementById('user-nav');

  if (!token) {
    alert('Você precisa estar logado como empresa para cadastrar vagas.');
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
        `;
    }
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const titulo = document.getElementById('titulo').value.trim();
    const descricao = document.getElementById('descricao').value.trim();
    const requisitos = document.getElementById('requisitos').value.trim();
    const salario = document.getElementById('salario').value.trim();

    if (!titulo || !descricao) {
      alert('Preencha todos os campos obrigatórios.');
      return;
    }

    try {
      const response = await fetch(`http://localhost/A3/baracity-empregos/api/cadastrar_vagas.php?token=${token}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          titulo,
          descricao,
          requisitos,
          salario
        })
      });

      const resultado = await response.json();

      if (response.ok) {
        form.reset();
        window.location.href = 'painel_empresa.html';
      } else {
        alert(`Erro: ${resultado.erro}`);
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
      alert('Erro ao conectar com o servidor.');
    }
  });
});

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}