/**
 * Method for calculating the missing "volt", "watt" and/or "ampere" value.
 *
 * @param    $volt
 * @param    $watt
 * @param    $ampere
 * @returns  void
 */
function vwa_autocalc($volt, $watt, $ampere) {
    var vals = {
        volt: $volt.getValue().toNumber(),
        watt: $watt.getValue().toNumber(),
        ampere: $ampere.getValue().toNumber()
    };
    
    if (vals.volt === 0 && vals.watt !== 0 && vals.ampere !== 0) {
        $volt.setValue((vals.watt / vals.ampere).roundWithDecimals(2));
    }
    
    if (vals.volt !== 0 && vals.watt === 0 && vals.ampere !== 0) {
        $watt.setValue((vals.volt * vals.ampere).roundWithDecimals(2));
    }
    
    if (vals.volt !== 0 && vals.watt !== 0 && vals.ampere === 0) {
        $ampere.setValue((vals.watt / vals.volt).roundWithDecimals(2));
    }
}