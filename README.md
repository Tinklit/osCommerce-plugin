Istruzioni di installazione:

1. Estrarre i file sul proprio PC.

2. Copiare i file nella cartella root di OsCommerce.

3. Seguire le istruzioni per modificare 4 file:
 - admin/includes/database_tables.txt per modifiche a admin/includes/database_tables.php
 - admin/orders.txt per modifiche a admin/orders.php
 - includes/database_tables.txt per modifiche a includes/database_tables.php
 - account.txt per modifiche a account.php

4. Eseguire la query SQL install.sql in phpMyAdmin (all'interno di cpanel).

5. Installare il modulo Tinkl.it dal admin di OsCommerce.

6. Inserire il Client ID e il Token.



-------------------------------------------------------------------

La conferma del pagamento è automatica e lo status dell'ordine viene aggiornato in base alle impostazioni del modulo. Per modificare le impostazioni ed utilizzare il modulo è necessario creare un terminale dal proprio profilo a www.tinkl.it. Il client id e token vanno inseriti nelle impostazini modulo su modules->payment. E' anche possibile selezionare le preferenze per quanto riguarda lo stato dell'ordine.



     `:oyhssssso+/-`            sMM-      :so                 dMm`        hMN.         os:  dMN`   
    .+mMMMhsssooooooso/`        .MMm::::`  `-.    ``-::-`     /MM+        :MMo          .-` +MMy::::
  .+shMMMNsso+///+osssss/`      hMNyyyys  ::-   -smNNNNMNy.  `NMd  `..`   mMN`        .::. `NMmyyyy+
 :ssyNMMMyo+///+osssssssso.    :MMo      /MM+ .hMmo-.`-sMMd  oMM:`://:`  +MM/         hMN. oMM:     
-ssshMMNy////+ossssssssssso`   dMN`     `mMm``mMm.     .MMh .NNh://-`   `NMd         :MMo .NMh      
ossyNMh+////ossssssssssssss:  +MM+      oMM/ oMM:      sMM- yNo//:`     sMM:         dMm` yMM.      
sssdMMNo////+ssssssssssssss/ `NMd      .NMh .NMh      -MMy :MMo://-`   .MMh         +MM/ -MMs       
osyMMMNyo////+ossssssssssss: :MMd`     yMM- yMM-      hMN. dMN` -///.  +MMy`       `NMd  oMMs`      
-smMMMhsss+////+sssssssssso` `hMMmh`  -MMs :MMs      /MMo /MM+   `://:``dMMmy ./:  sMM:  .dMMms     
 :NMMNysssso////+ossssssso.    -oyo   +yy` +yy`      oyy` syy      ----``:oy+ .// `yyo    `:oy/     
  -dMhsssssss+////+sssss/`                                                                          
    -+ssssssssooooooso/`            
       .:+ossssso+/-`             
