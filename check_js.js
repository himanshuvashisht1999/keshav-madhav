const fs = require('fs');
const content = JSON.parse(fs.readFileSync('output.json', 'utf8'));

content.packing.cartons.forEach((carton, cIdx) => {
    carton.items.forEach((item, iIdx) => {
        let detail = item.detail;
        if (!detail) {
            console.log(`Carton ${cIdx} Item ${iIdx} NO DETAIL!`);
            return;
        }
        let ops = detail.order_product_set;
        let prod = detail.product || (ops ? ops.product : null);
        let col = detail.colors || (ops ? ops.colors : null);
        let size = detail.size_measurement || (ops ? ops.size_measurement : null);
        
        let design = null;
        if(prod && prod.design_number) design = prod.design_number;
        else if(ops && ops.design_number) design = ops.design_number;
        else if(detail.design_number) design = detail.design_number;

        if (!design) {
            console.log(`Carton ${cIdx} Item ${iIdx} NO DESIGN! ops=`, !!ops, 'prod=', !!prod, 'col=', !!col, 'size=', !!size);
        } else {
            console.log(`Carton ${cIdx} Item ${iIdx} Design:`, design);
        }
    });
});
