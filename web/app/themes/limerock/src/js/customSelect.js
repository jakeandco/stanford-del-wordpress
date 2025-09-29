import customSelect from 'custom-select';
import 'custom-select/build/custom-select.css';

export function setup() {
    console.log('Custom Select');

    var customSelect = require('custom-select').default;
    customSelect('.custom-select select');
}

export function teardown() {
    
}