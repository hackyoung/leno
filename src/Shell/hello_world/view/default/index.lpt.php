<extend name="global._layout.default">
    <fragment name="body">
        <style>
        .hello {
            font-size: 20px;
            margin-top: 100px;
            text-shadow: 3px 3px 3px rgba(0, 0, 0, 0.8);
        }
        </style>
        <center class="hello">{$hello}</center>
    </fragment>
</extend>
