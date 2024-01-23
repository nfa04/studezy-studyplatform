function removeDocument(documentID) {
    new APIRequest("remove_doc").sendAndTrigger({
        id: documentID
    });
}