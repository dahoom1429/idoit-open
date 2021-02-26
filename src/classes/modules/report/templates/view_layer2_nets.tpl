<table class="mainTable" id="report_view__layer2_nets">
    <thead>
    <tr>
        <th>[{isys type='lang' ident='LC__CMDB__OBJTYPE__LAYER2_NET'}]</th>
        <th>[{isys type='lang' ident='LC__CATD__PORT'}]</th>
        <th>[{isys type='lang' ident='LC__UNIVERSAL__OBJECT_TITLE'}]</th>
        <th>[{isys type='lang' ident='LC__REPORT__VIEW__LAYER2_NETS__IP_ADDRESSES'}]</th>
        <th>[{isys type='lang' ident='LC__CMDB__OBJTYPE__LAYER3_NET'}]</th>
    </tr>
    </thead>
    <tbody>
    [{foreach $data as $row}]
        <tr>
            <td>[{$row[0]}]</td>
            <td>[{$row[1]}]</td>
            <td>[{$row[2]}]</td>
            <td>[{$row[3]}]</td>
            <td>[{$row[4]}]</td>
        </tr>
    [{/foreach}]
    </tbody>
</table>

<style type="text/css">
    #report_view__layer2_nets span {
        color: #aaa;
    }
</style>
