/**
 * @see  ID-7209 and DOKU-304  Overwriting default styles.
 */
CKEDITOR.stylesSet.add('i-doit', [
    {name: 'Marker', element: 'span', attributes: {'class': 'marker'}},
    {name: 'Monotype', element: 'span', attributes: {'class': 'text-monospace font-monospace'}},
    {name: 'Code', element: 'pre'},
    {name: 'Deleted Text', element: 'del'},
    {name: 'Inserted Text', element: 'ins'}
]);

