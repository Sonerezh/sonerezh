function SongsManager () {
    // Let's remove the legacy IndexedDB database!
    const DB_NAME = 'songs-db';
    let req = indexedDB.open(DB_NAME);
    req.onupgradeneeded = function (e) {
        e.target.transaction.abort();
    };

    req.onsuccess = function () {
        let del = indexedDB.deleteDatabase(DB_NAME);
        del.onsuccess = function () {
            console.log('Deleted IndexedDB database successfully.');
        };
        del.onerror = function (e) {
            console.log('Error occurred while deleting IndexedDB database: ' + e.target.errorCode);
        };
    };
}