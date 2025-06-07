document.querySelector('form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const nome  = document.querySelector('#nome').value;
  const email = document.querySelector('#email').value;
  const senha = document.querySelector('#senha').value;
  const tipo  = document.querySelector('#tipo').value;

  console.log(nome)
  console.log(email)
  console.log(senha)
  console.log(tipo)

  try {
    const response = await fetch('http://localhost/A3/baracity-empregos/api/cadastrar.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ nome, email, senha, tipo })
    });

    const result = await response.json();

    if (response.ok) {
      alert('Cadastro realizado com sucesso!');
      window.location.href = 'login.html';
    } else {
      alert(result.erro || 'Erro ao cadastrar');
    }
  } catch (err) {
    console.error(err);
    alert('Erro ao conectar com o servidor');
  }
});
