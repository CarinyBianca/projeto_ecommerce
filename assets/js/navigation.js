document.addEventListener('DOMContentLoaded', function() {
    // Cria o container dos botões
    const navButtons = document.createElement('div');
    navButtons.className = 'nav-buttons';

    // Botão Voltar
    const backButton = document.createElement('button');
    backButton.className = 'nav-button';
    backButton.innerHTML = '<i class="fas fa-arrow-left"></i>';
    backButton.title = 'Voltar';
    backButton.onclick = () => history.back();

    // Botão Avançar
    const forwardButton = document.createElement('button');
    forwardButton.className = 'nav-button';
    forwardButton.innerHTML = '<i class="fas fa-arrow-right"></i>';
    forwardButton.title = 'Avançar';
    forwardButton.onclick = () => history.forward();

    // Adiciona os botões ao container
    navButtons.appendChild(backButton);
    navButtons.appendChild(forwardButton);

    // Adiciona o container ao body
    document.body.appendChild(navButtons);

    // Atualiza o estado dos botões
    function updateButtonStates() {
        backButton.disabled = !history.length;
        forwardButton.disabled = !window.history.state;
    }

    // Atualiza o estado inicial dos botões
    updateButtonStates();

    // Adiciona listener para atualizar os botões quando a história mudar
    window.addEventListener('popstate', updateButtonStates);
});
