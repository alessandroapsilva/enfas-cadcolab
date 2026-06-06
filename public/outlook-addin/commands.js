(function() {
    Office.initialize = function(reason) {
        console.log("Suplemento ENFAS iniciado");
    };
    
    async function getSignatureFromServer() {
        const email = Office.context.mailbox.userProfile.emailAddress;
        const token = sessionStorage.getItem('signature_token_' + email);
        
        if(!token) return null;
        
        const response = await fetch(`/api/signature?email=${encodeURIComponent(email)}&token=${token}`);
        if(!response.ok) return null;
        const data = await response.json();
        return data.signature;
    }
    
    async function insertSignature() {
        const htmlSignature = await getSignatureFromServer();
        
        if(!htmlSignature) {
            Office.context.mailbox.displayNotificationMessage(
                "Assinatura não encontrada. Solicite ao TI para sincronizar sua assinatura.",
                { type: "errorMessage" }
            );
            return;
        }
        
        const item = Office.context.mailbox.item;
        const currentBody = await new Promise(resolve => {
            item.body.getAsync(Office.CoercionType.Html, function(result) {
                if(result.status === Office.AsyncResultStatus.Succeeded) {
                    resolve(result.value);
                } else {
                    resolve("");
                }
            });
        });
        
        const newBody = currentBody + htmlSignature;
        
        item.body.setAsync(newBody, Office.CoercionType.Html, function(result) {
            if(result.status === Office.AsyncResultStatus.Succeeded) {
                Office.context.mailbox.displayNotificationMessage(
                    "Assinatura inserida com sucesso!",
                    { type: "informationalMessage" }
                );
            } else {
                Office.context.mailbox.displayNotificationMessage(
                    "Erro ao inserir assinatura.",
                    { type: "errorMessage" }
                );
            }
        });
    }
    
    window.insertSignature = insertSignature;
})();
