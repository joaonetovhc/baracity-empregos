document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-login');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value.trim();
    const tipo = document.getElementById('tipo').value;

    try {
      const resposta = await fetch('http://localhost/A3/baracity-empregos/api/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, senha, tipo })
      });

      const dados = await resposta.json();

      if (resposta.ok) {
        localStorage.setItem('token', dados.token);
        localStorage.setItem('tipo', dados.usuario.tipo);
        localStorage.setItem('nome', dados.usuario.nome);

        if (dados.usuario.tipo === 'candidato') {
          window.location.href = 'listar_vagas.html';
        } else if (dados.usuario.tipo === 'empresa') {
          window.location.href = 'painel_empresa.html';
        } else {
          window.location.href = 'listar_vagas.html';
        }
      } else {
        alert(dados.erro || 'Erro no login');
      }
    } catch (erro) {
      console.error('Erro ao conectar com o servidor', erro);
      alert('Erro ao conectar com o servidor');
    }
  });
});
