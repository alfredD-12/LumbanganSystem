const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});


(function(){
    const bouncyEls = Array.from(document.querySelectorAll('.bouncy'));
    if (!bouncyEls.length) return;

    function parseWords(el){
        const data = el.getAttribute('data-words');
        if (data && data.trim()) return data.split('|').map(s => s.trim()).filter(Boolean);
        
        
        
        
        
        const letters = Array.from(el.querySelectorAll('span')).map(s => s.textContent || '').join('');
        return [letters];
    }

    const datasets = bouncyEls.map(el => {
        const words = parseWords(el);
        el.innerHTML = '';
        const span = document.createElement('span');
        span.className = 'typewriter-text';
        el.appendChild(span);
        return {
            el,
            container: span,
            words,
            
            typeSpeed: parseInt(el.getAttribute('data-type-speed')) || 50,
            deleteSpeed: parseInt(el.getAttribute('data-delete-speed')) || 30,
            pauseAfter: parseInt(el.getAttribute('data-pause')) || 900,
        };
    });

    
    
    datasets.forEach((d, idx) => {
        (async function loop(){
            let wi = 0;
            while(true){
                const word = d.words[wi];
                d.container.innerHTML = '';
                const chars = Array.from(word).map(ch => {
                    const s = document.createElement('span');
                    s.className = 'tw-char';
                    s.textContent = ch;
                    d.container.appendChild(s);
                    return s;
                });

                for (let i=0;i<chars.length;i++){
                    await new Promise(r => setTimeout(r, d.typeSpeed + i * 18));
                    chars[i].classList.add('in');
                }

                await new Promise(r => setTimeout(r, d.pauseAfter));

                for (let i=chars.length-1;i>=0;i--){
                    chars[i].classList.remove('in');
                    chars[i].classList.add('out');
                    await new Promise(r => setTimeout(r, d.deleteSpeed + (chars.length - i) * 12));
                }

                await new Promise(r => setTimeout(r, 220));
                wi = (wi + 1) % d.words.length;
            }
        })();
    });
})();