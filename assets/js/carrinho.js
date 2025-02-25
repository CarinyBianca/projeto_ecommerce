// Função para adicionar produto ao carrinho
function adicionarAoCarrinho(produtoId) {
    const formData = new FormData();
    formData.append('produto_id', produtoId);
    
    // Desabilita o botão durante a requisição
    const botao = document.querySelector(`button[data-produto-id="${produtoId}"]`);
    if (botao) {
        botao.disabled = true;
        botao.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
    }
    
    const fetchConfig = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    fetch('adicionar_ao_carrinho.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
        ...fetchConfig
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza o contador do carrinho no header
            const contadorCarrinho = document.getElementById('contador-carrinho');
            if (contadorCarrinho) {
                contadorCarrinho.textContent = data.quantidade_total;
                contadorCarrinho.style.display = data.quantidade_total > 0 ? 'inline-block' : 'none';
            }

            // Se estiver na página do carrinho, atualiza os valores
            const tabelaCarrinho = document.querySelector('.table-responsive');
            if (tabelaCarrinho) {
                window.location.reload();
            }
            
            // Mostra mensagem de sucesso
            mostrarMensagem(`${data.produto.nome} adicionado ao carrinho!`, 'success');
        } else {
            mostrarMensagem(data.message || 'Erro ao adicionar produto ao carrinho', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao adicionar produto ao carrinho. Tente novamente.', 'danger');
    })
    .finally(() => {
        // Reabilita o botão
        if (botao) {
            botao.disabled = false;
            botao.innerHTML = '<i class="fas fa-cart-plus"></i> Adicionar';
        }
    });
}

// Função para mostrar mensagens
function mostrarMensagem(mensagem, tipo) {
    const alertContainer = document.querySelector('.alert-container');
    if (!alertContainer) return;

    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo} alert-dismissible fade show`;
    alert.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertContainer.appendChild(alert);

    // Remove a mensagem após 5 segundos
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
    }, 5000);
}

// Função para atualizar quantidade
function atualizarQuantidade(produtoId, novaQuantidade) {
    if (!produtoId || novaQuantidade < 1) {
        console.error('ID do produto ou quantidade inválidos');
        return;
    }

    console.log('Atualizando quantidade:', { produtoId, novaQuantidade });

    const formData = new FormData();
    formData.append('produto_id', produtoId);
    formData.append('quantidade', novaQuantidade);

    // Encontra a linha do produto
    const controle = document.querySelector(`.quantidade-controle button[data-produto-id="${produtoId}"]`).closest('.quantidade-controle');
    if (controle) {
        // Desabilita os botões durante a atualização
        const botoes = controle.querySelectorAll('button');
        botoes.forEach(btn => btn.disabled = true);
        
        // Adiciona classe de loading
        controle.classList.add('loading');
    }

    fetch('atualizar_carrinho.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta do servidor:', data);
        
        if (data.success) {
            // Atualiza a quantidade exibida
            if (controle) {
                const quantidadeElement = controle.querySelector('.quantidade-valor');
                if (quantidadeElement) {
                    quantidadeElement.textContent = novaQuantidade;
                }
            }

            // Atualiza o subtotal do produto
            const linha = controle?.closest('tr');
            if (linha) {
                const subtotalElement = linha.querySelector('td:nth-last-child(2)');
                if (subtotalElement) {
                    subtotalElement.textContent = formatarMoeda(data.subtotal);
                }
            }

            // Atualiza os totais gerais
            atualizarTotais();

            // Atualiza o contador do carrinho no header
            const contadorCarrinho = document.getElementById('contador-carrinho');
            if (contadorCarrinho) {
                contadorCarrinho.textContent = data.quantidade_total;
                contadorCarrinho.style.display = data.quantidade_total > 0 ? 'inline-block' : 'none';
            }

            mostrarMensagem('Carrinho atualizado com sucesso!', 'success');
        } else {
            mostrarMensagem(data.message || 'Erro ao atualizar carrinho', 'danger');
            // Reverte a quantidade
            if (controle) {
                const quantidadeElement = controle.querySelector('.quantidade-valor');
                if (quantidadeElement) {
                    quantidadeElement.textContent = data.quantidade_atual || novaQuantidade;
                }
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar carrinho. Tente novamente.', 'danger');
    })
    .finally(() => {
        if (controle) {
            // Remove classe de loading
            controle.classList.remove('loading');
            
            // Reabilita os botões
            const botoes = controle.querySelectorAll('button');
            botoes.forEach(btn => btn.disabled = false);
        }
    });
}

// Função para atualizar quantidade na UI
function atualizarQuantidadeUI(produtoId, novaQuantidade, subtotal) {
    const quantidadeElement = document.querySelector(`.quantidade-controle .quantidade-valor[data-produto-id="${produtoId}"]`);
    const subtotalElement = document.querySelector(`tr[data-produto-id="${produtoId}"] td:nth-last-child(2)`);
    
    if (quantidadeElement) {
        quantidadeElement.textContent = novaQuantidade;
    }
    if (subtotalElement) {
        subtotalElement.textContent = formatarMoeda(subtotal);
    }
}

// Função para atualizar totais
function atualizarTotais() {
    const linhas = document.querySelectorAll('tr[data-produto-id]');
    let subtotal = 0;

    linhas.forEach(linha => {
        const quantidade = parseInt(linha.querySelector('input[type="number"]').value);
        const preco = parseFloat(linha.querySelector('.preco').textContent.replace(/[^\d,]/g, '').replace(',', '.'));
        subtotal += quantidade * preco;
    });

    // Atualiza subtotal
    document.getElementById('subtotal').textContent = formatarMoeda(subtotal);
    
    // Se o frete já foi calculado, soma ao total
    const freteElement = document.getElementById('valor-frete');
    let total = subtotal;
    
    if (document.getElementById('info-frete').style.display !== 'none') {
        const valorFrete = parseFloat(freteElement.textContent.replace(/[^\d,]/g, '').replace(',', '.'));
        total += valorFrete;
    }
    
    // Atualiza total
    document.getElementById('valor-total').textContent = formatarMoeda(total);
}

// Função para formatar moeda
function formatarMoeda(valor) {
    return `R$ ${valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Função para remover produto
function removerProduto(produtoId) {
    if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
        return;
    }

    const formData = new FormData();
    formData.append('produto_id', produtoId);

    fetch('remover_do_carrinho.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove a linha do produto
            const linha = document.querySelector(`tr[data-produto-id="${produtoId}"]`);
            if (linha) {
                linha.remove();
            }

            // Atualiza o contador do carrinho
            const contadorCarrinho = document.getElementById('contador-carrinho');
            if (contadorCarrinho) {
                contadorCarrinho.textContent = data.carrinho_quantidade;
                contadorCarrinho.style.display = data.carrinho_quantidade > 0 ? 'inline-block' : 'none';
            }

            // Se o carrinho estiver vazio, recarrega a página
            if (data.carrinho_quantidade === 0) {
                window.location.reload();
            } else {
                // Recalcula os totais
                atualizarTotais();
            }

            mostrarMensagem('Item removido do carrinho com sucesso!', 'success');
        } else {
            mostrarMensagem(data.message || 'Erro ao remover item do carrinho', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao remover item do carrinho', 'danger');
    });
}

// Função para finalizar compra
function finalizarCompra() {
    const fetchConfig = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    fetch('finalizar_compra.php', {
        method: 'POST',
        credentials: 'include',
        ...fetchConfig
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Compra finalizada com sucesso!');
            window.location.reload(); // Recarrega a página para limpar o carrinho
        } else {
            alert(data.message || 'Erro ao finalizar compra');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao finalizar compra. Tente novamente.');
    });
}

// Função para formatar CEP
function formatarCEP(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length > 5) {
        cep = cep.substring(0, 5) + '-' + cep.substring(5);
    }
    return cep;
}

// Função para calcular frete
function calcularFrete() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        mostrarMensagem('Digite um CEP válido', 'warning');
        return;
    }

    // Mostra indicador de carregamento
    const btnCalcular = document.querySelector('button[onclick="calcularFrete()"]');
    const iconOriginal = btnCalcular.innerHTML;
    btnCalcular.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnCalcular.disabled = true;

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                throw new Error('CEP não encontrado');
            }

            // Atualiza endereço
            const enderecoCompleto = `${data.logradouro}, ${data.bairro}, ${data.localidade}/${data.uf}`;
            document.getElementById('endereco-completo').textContent = enderecoCompleto;
            document.getElementById('endereco-entrega').style.display = 'block';

            // Calcula valor do frete baseado na região
            let valorFrete = 20.00; // Valor base

            // Ajusta valor do frete por região
            switch (data.uf) {
                case 'SP':
                case 'RJ':
                case 'MG':
                    valorFrete = 15.00;
                    break;
                case 'RS':
                case 'SC':
                case 'PR':
                    valorFrete = 18.00;
                    break;
                default:
                    valorFrete = 20.00;
            }

            console.log('Valor do frete:', valorFrete); // Debug

            // Atualiza prazo de entrega
            document.getElementById('dias-entrega').textContent = 'Prazo de entrega: 5 a 7 dias úteis';
            
            // Mostra seção de frete
            document.getElementById('info-frete').style.display = 'block';

            // Atualiza valores
            document.getElementById('valor-frete').textContent = formatarMoeda(valorFrete);
            
            // Atualiza total
            const subtotalText = document.getElementById('subtotal').textContent;
            const subtotal = parseFloat(subtotalText.replace(/[^\d,]/g, '').replace(',', '.'));
            const total = subtotal + valorFrete;
            document.getElementById('valor-total').textContent = formatarMoeda(total);

            // Salva o frete calculado
            const formData = new FormData();
            formData.append('cep', cep);
            formData.append('valor_frete', valorFrete);
            
            fetch('salvar_frete.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensagem('Frete calculado com sucesso!', 'success');
                }
            })
            .catch(error => console.error('Erro ao salvar frete:', error));
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao calcular o frete. Verifique o CEP.', 'danger');
        })
        .finally(() => {
            // Restaura botão
            btnCalcular.innerHTML = iconOriginal;
            btnCalcular.disabled = false;
        });
}

// Adiciona máscara ao CEP
document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            this.value = formatarCEP(this.value);
        });
    }
});

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando carrinho...');
    
    // Listener para botão de diminuir quantidade
    document.querySelectorAll('.diminuir-quantidade').forEach(button => {
        button.addEventListener('click', function() {
            const produtoId = this.getAttribute('data-produto-id');
            const quantidadeElement = this.closest('.quantidade-controle').querySelector('.quantidade-valor');
            let quantidade = parseInt(quantidadeElement.textContent);
            
            if (quantidade > 1) {
                quantidade--;
                atualizarQuantidade(produtoId, quantidade);
            }
        });
    });
    
    // Listener para botão de aumentar quantidade
    document.querySelectorAll('.aumentar-quantidade').forEach(button => {
        button.addEventListener('click', function() {
            const produtoId = this.getAttribute('data-produto-id');
            const quantidadeElement = this.closest('.quantidade-controle').querySelector('.quantidade-valor');
            let quantidade = parseInt(quantidadeElement.textContent);
            
            quantidade++;
            atualizarQuantidade(produtoId, quantidade);
        });
    });

    // Listener para botões de remover item
    document.querySelectorAll('.remover-item').forEach(button => {
        button.addEventListener('click', function() {
            const produtoId = this.getAttribute('data-produto-id');
            if (produtoId) {
                removerProduto(produtoId);
            }
        });
    });

    // Adiciona evento ao botão de finalizar compra
    const btnFinalizarCompra = document.querySelector('.btn-success[onclick="finalizarCompra()"]');
    if (btnFinalizarCompra) {
        btnFinalizarCompra.removeAttribute('onclick');
        btnFinalizarCompra.addEventListener('click', finalizarCompra);
    }

    // Inicializa tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
