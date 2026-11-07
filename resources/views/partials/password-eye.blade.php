<script>
    (function () {
        var open = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
        var shut = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.9 10.9 0 0112 20C5 20 1 12 1 12a18.5 18.5 0 015.06-5.94M9.9 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M1 1l22 22"/></svg>';
        document.querySelectorAll('input[type=password]').forEach(function (inp) {
            var w = document.createElement('div');
            w.style.position = 'relative';
            inp.parentNode.insertBefore(w, inp);
            w.appendChild(inp);
            inp.style.paddingRight = '3rem';
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = open;
            b.setAttribute('aria-label', 'Parolni ko\'rsatish');
            b.style.cssText = 'position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#6b7280;background:none;border:0;cursor:pointer;padding:4px';
            b.onclick = function () {
                var show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                b.innerHTML = show ? shut : open;
            };
            w.appendChild(b);
        });
    })();
</script>