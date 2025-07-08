function formatarValorEmReais(valor) {
    if (!valor) return '';
    
    // Remove tudo que não for número
    valor = valor.toString().replace(/\D/g, '');
    
    // Se não há valor, retorna vazio
    if (valor === '') return '';
    
    // Converte para número e divide por 100 para ter os centavos
    let numero = parseInt(valor) / 100;
    
    // Formata usando toLocaleString para padrão brasileiro
    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).replace('\u00A0', ' '); // <- NBSP substituído por espaço normal

}

document.addEventListener('DOMContentLoaded', function() {

    $('.protocolDisplayMask').on('input', function(e) {
        e.target.value = formatarValorEmReais(e.target.value);
    });
    $('#numberProtocolInput').on('input', function(e) {
        e.target.value = formatarValorEmReais(e.target.value);
    });

    document.querySelectorAll('.protocolDisplayMask').forEach(element => {
        // if(element.nodeName === 'TD')
        //     element.textContent = formatarValorEmReais(element.textContent);
        if(element.nodeName === 'INPUT')
            element.value = formatarValorEmReais(element.value);
        // if(element.nodeName === 'SPAN')
        //     element.textContent = formatarValorEmReais(element.textContent);

    });
});