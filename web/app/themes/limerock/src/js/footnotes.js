import Collapse from 'bootstrap/js/dist/collapse';


export function setup() {
    const collapseElementList = document.querySelectorAll('.collapse');

    // Create collapse instance
    const collapseList = [...collapseElementList].map(collapseEl => new Collapse(collapseEl, {
        toggle: false
    }))

    const collapseList2 = [...collapseElementList].map(function(collapseElement) {
        document.addEventListener('click', function (event) {
            const button = document.querySelector('[data-bs-target*="#footnote-popup"][aria-expanded="false"]');
            
            if (collapseElement.classList.contains('show') &&
                !collapseElement.contains(event.target) &&
                !button.contains(event.target)) {
        
              const collapseInstance = Collapse.getInstance(collapseElement);
              collapseInstance.hide();
            }

            let curentHolder = event.target.closest('.footnote-item');

            if (!curentHolder) {
                return;
            }

            let curentPopup = curentHolder.querySelector('.footnote-popup');

            let curentHolderLeft = curentHolder.getBoundingClientRect().left;
            let curentHolderTop = curentHolder.getBoundingClientRect().top;

            curentPopup.style.left = `calc(45px - ${Math.round(curentHolderLeft)}px)`;

            let wHeight = screen.height;
            let wWidth = screen.width;
            
            if ( curentHolderLeft > (wWidth / 2 - 396) ) {
                curentHolder.classList.add('to-left');
            }

            if ( curentHolderTop < (wHeight / 2) ) {
                curentHolder.classList.add('to-bottom');
            }
        });
    })
    
}

export function teardown() {
    
}