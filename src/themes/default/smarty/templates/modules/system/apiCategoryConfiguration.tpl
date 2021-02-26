[{*Render react component*}]
[{isys type="f_react_bridge" component="idoit.addon.api.Page" params=['apiKey' => $apiKey, 'translations' => $translations]}]

[{*Some styles needed for the react component*}]
<style type="text/css">
    .categoryDefinitionTable {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 3px;
        border: 1px solid darkgrey;
    }

    .categoryDefinitionTable td {
        padding: 10px;
        margin: 0;
    }

    .categoryDefinitionTable thead tr, .categoryDefinitionTable tr.primary {
        text-align: center !important;
        font-weight: bolder !important;
        background-color: grey !important;
        color: white !important;
        border-bottom: 4px solid darkgrey !important;
    }

    .categoryDefinitionTable tbody td {
        border-right: 1px solid darkgrey;
        text-align: left;
    }

    .categoryDefinitionTable tbody td:last-child {
        border-right: none;
    }

    .categoryDefinitionTable tbody tr {
        background-color: white;
    }

    .categoryDefinitionTable tbody tr:nth-child(even) {
        background-color: #f2f2f2
    }

    .apiConfigurationContainer pre {
        padding: 10px;
        white-space: pre-wrap;
    }

    .apiConfigurationContainer h1,
    .apiConfigurationContainer h2,
    .apiConfigurationContainer h3 {
        padding: 5px;
        border-bottom: 1px solid grey;
    }

    .apiConfigurationContainer h4 {
        padding: 10px;
    }

    .apiConfigurationContainer .section {
        margin-bottom: 20px;
    }

    .apiConfigurationContainer table a {
        text-decoration: underline dotted;
    }

    span.entityDescriptor {
        cursor: pointer;
        text-decoration: underline dotted;
    }
</style>
